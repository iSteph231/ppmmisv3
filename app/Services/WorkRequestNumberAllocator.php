<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use LogicException;
use RuntimeException;

class WorkRequestNumberAllocator
{
    public function allocate(string $month): string
    {
        if (! preg_match('/^\d{4}(0[1-9]|1[0-2])$/D', $month)) {
            throw new LogicException('A valid request month is required.');
        }
        if (DB::transactionLevel() === 0) {
            throw new LogicException('Request numbers must be allocated inside the creation transaction.');
        }

        DB::table('work_request_counters')->upsert(
            [['month' => $month, 'last_number' => null]], ['month'], ['month'],
        );
        $counter = DB::table('work_request_counters')->where('month', $month)->lockForUpdate()->first();
        $lastNumber = $counter->last_number;

        if ($lastNumber === null) {
            $lastNumber = 0;
            foreach (DB::table('work_requests')->where('request_number', 'like', "WR-{$month}-%")->lockForUpdate()->pluck('request_number') as $number) {
                if (preg_match('/^WR-'.preg_quote($month, '/').'-(\d+)$/D', $number, $matches)) {
                    $lastNumber = max($lastNumber, (int) $matches[1]);
                }
            }
        }

        if ($lastNumber >= 9999) {
            throw new RuntimeException('The monthly work request number range is exhausted.');
        }

        $nextNumber = $lastNumber + 1;
        DB::table('work_request_counters')->where('month', $month)->update(['last_number' => $nextNumber]);

        return 'WR-'.$month.'-'.str_pad((string) $nextNumber, 4, '0', STR_PAD_LEFT);
    }
}
