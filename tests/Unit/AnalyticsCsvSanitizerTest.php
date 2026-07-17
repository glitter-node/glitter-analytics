<?php
namespace Modules\Glitter\Analytics\Tests\Unit;
use Modules\Glitter\Analytics\Services\AnalyticsCsvSanitizer;
use PHPUnit\Framework\TestCase;
class AnalyticsCsvSanitizerTest extends TestCase
{
    public function test_formula_prefixes_are_neutralized(): void
    {
        $csv = new AnalyticsCsvSanitizer();
        foreach (["=1+1", "+SUM(A1)", "-2+3", "@cmd", "\tformula", "\rformula"] as $value) {
            $this->assertSame(chr(39).$value, $csv->cell($value));
        }
    }

    public function test_safe_values_and_csv_quoting_are_preserved(): void
    {
        $csv = new AnalyticsCsvSanitizer();
        $this->assertSame("/safe,quoted\"line\r\nnext", $csv->cell("/safe,quoted\"line\r\nnext"));
        $stream = fopen("php://temp", "w+");
        fputcsv($stream, $csv->row(["/safe,quoted\"line\r\nnext"]), ",", "\"", "", "\r\n");
        rewind($stream); $encoded = stream_get_contents($stream); fclose($stream);
        $this->assertSame("\"/safe,quoted\"\"line\r\nnext\"\r\n", $encoded);
    }
}
