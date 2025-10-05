<?php
namespace App\Services;

use Carbon\Carbon;
use App\Models\Product;
use Illuminate\Support\Facades\Log;

class ProductImporterService {

    const MAXVALUECOST = 1000;
    const MINCOST = 5; const MINSTOCK=10;
    const EXPECTERHEADERS = ['Product Code', 'Product Name', 'Product Description', 'Stock', 'Cost in GBP', 'Discontinued'];

    protected string $filePath;
    protected bool $testMode;
    protected array $errors = [];
    protected int $processed = 0;
    protected int $inserted = 0;
    protected int $skipped = 0;


    /**
     * initialize service class in order to process csv data file
     * @created 2025-10-04
     * @params string, bool
     * @return App\Service\ProductImporterService
     */
    public function __construct(string $filePath, bool $testMode = false)
    {
        $this->filePath = $filePath;
        $this->testMode = $testMode;
    }

    /**
     * import data and validate each scenarous in test mode and production mode
     * @created 2025-10-04
     * @author Kareem Lorenzana
     * @params void
     * @return array
     */
    public function import(): array
    {
        if (!file_exists($this->filePath)) {
            Log::warning( Carbon::now()."CSV file not found at {$this->filePath}");
            throw new \Exception("CSV file not found at {$this->filePath}");
        }

        $handle = fopen($this->filePath, 'r');
        $handle = fopen($this->filePath, 'r');
        if (!$handle) {
            Log::error( "Unable to open CSV file at {$this->filePath}");
            throw new \Exception("Unable to open CSV file at {$this->filePath}");
        }
        $header = fgetcsv($handle);
        $expectedHeaders = self::EXPECTERHEADERS;
        if ($header !== $expectedHeaders) {
            Log::error( Carbon::now()." CSV headers do not match expected format: ".json_encode($header));
            throw new \Exception("CSV headers do not match expected format.");
        }

        while (($row = fgetcsv($handle)) !== false) {
            $this->processed++;
            //change to UTF8 before insert
            $row = array_map(fn($value) => mb_convert_encoding($value, 'UTF-8', 'auto'), $row);
            if (count($row) < 6) {
                Log::error(Carbon::now().' Row has insufficient columns');
                $this->skipped++;
                $this->errors[] = [
                    'row' => $row,
                    'error' => 'Row has insufficient columns'
                ];
                continue;
            }
            $product = $this->mapRowToProduct($row);

            if (!$this->passesBusinessRules($product)) {
                $this->skipped++;
                continue;
            }

            if ($this->testMode) {
                $this->inserted++; // Simulate success
                continue;
            }

            try {
                if (Product::where('strProductCode', $product['strProductCode'])->exists()) {
                    $this->errors[] = [
                        'row' => $product,
                        'error' => "Product already exist!"
                    ];
                    $this->skipped++;
                    continue;
                }
                Product::create($product);
                $this->inserted++;
            } catch (\Exception $e) {
                $messageError = $e->getMessage();
                Log::error(Carbon::now() . " Failed to insert product: " . json_encode($product) . " | Error: $messageError");
                $this->errors[] = [
                    'row' => $row,
                    'error' => $messageError
                ];
            }
        }

        fclose($handle);
        Log::info(Carbon::now() . " Import summary: processed={$this->processed}, inserted={$this->inserted}, skipped={$this->skipped}");

        return [
            'processed' => $this->processed,
            'inserted' => $this->inserted,
            'skipped' => $this->skipped,
            'errors' => $this->errors,
        ];
    }

    /**
     * map to database columns each valid entry
     * @created 2025-10-04
     * @author Kareem Lorenzana
     * @params array
     * @return array
     */
    protected function mapRowToProduct(array $row): array
    {
        return [
            'strProductCode' => trim($row[0]),
            'strProductName' => trim($row[1]),
            'strProductDesc' => trim($row[2]),
            'intStock' => is_numeric($row[3]) ? (int) $row[3] : null,
            'decCostGBP' => $this->normalizeCost($row[4]),
            'dtmAdded' => Carbon::now(),
            'dtmDiscontinued' => strtolower(trim($row[5] ?? '')) === 'yes' ? Carbon::now() : null,
        ];
    }

    /**
     * get clean values for currency columns
     * @created 2025-10-04
     * @author Kareem Lorenzana
     * @params mixed
     * @return float
     */
    protected function normalizeCost($value): ?float
    {
        $clean = preg_replace('/[^0-9.]/', '', $value);
        return is_numeric($clean) ? round((float) $clean, 2) : null;
    }

    /**
     * pass only columns with stock with more than MINSTOCK and cost beetween MINCOST  and skip all with more than  MAXVALUECOST
     * @created 2025-10-04
     * @author Kareem Lorenzana
     * @params array
     * @return bool
     */
    protected function passesBusinessRules(array $product): bool
    {
        $cost = $product['decCostGBP'];
        $stock = $product['intStock'];

        if ($cost === null || $stock === null) {
            $this->errors[] = [
                'row' => $product,
                'error' => "Cost and stock are null"
            ];
            return false;
        }

        if ($cost < self::MINCOST && $stock < self::MINSTOCK) {
            $this->errors[] = [
                'row' => $product,
                'error' => "cost are less than ".self::MINCOST." and stock are less than ".self::MINSTOCK."."
            ];
            return false;
        }

        if ($cost > self::MAXVALUECOST) {
            $this->errors[] = [
                'row' => $product,
                'error' => "cost is higher than ".self::MAXVALUECOST."."
            ];
            return false;
        }

        return true;
    }
}
