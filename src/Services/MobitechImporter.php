<?php

namespace Ragnarok\Mobitech\Services;

use Illuminate\Support\Carbon;
use Ragnarok\Mobitech\Models\SoftpayTransaction;
use Ragnarok\Mobitech\Models\Statistics;
use Ragnarok\Mobitech\Models\Transaction;
use Ragnarok\Sink\Traits\LogPrintf;

class MobitechImporter
{
    use LogPrintf;

    public function __construct()
    {
        $this->logPrintfInit('[MobitechImporter]: ');
    }

    /**
     * Importing transaction/statistics data from json file.
     *
     * @param string $id Chunk ID. Date on format YYYY-MM-DD
     * @param string $file Path to source file
     *
     * @return int Number of rows imported
     */
    public function import(string $id, string $file)
    {
        $subject = $this->getSubjectFromFilePath($file);
        $method = "import" . $subject;
        if ($subject && method_exists($this, $method)) {
            $json = json_decode(file_get_contents($file));
            call_user_func([$this, $method], $id, $json);
            return 1;
        }
        return 0;
    }

    protected function importSoftpayTransaction(string $id, object $json): void
    {
        SoftpayTransaction::create([
            'chunk_date'            => new Carbon($id),
            'operator_reference'    => strtoupper($json->OperatorReference),
            'departure'             => new Carbon ($json->Departure),
            'line_id'               => $json->LineId,
            'tour_id'               => $json->TourId,
            'stop_place_id_entry'   => $json->StopPlaceIdEntry,
            'stop_place_id_exit'    => $json->StopPlaceIdExit,
            'actor_id'              => $json->ActorId,
            'country_code'          => $json->CountryCode,
            'transaction_number'    => $json->TransactionNumber,
            'trailer'               => $json->Trailer,
            'tariff_class'          => $json->TariffClass,
            'receipt_id'            => $json->Identification->ReceiptId,
            'batch_number'          => $json->Identification->BatchNumber,
            'terminal_id'           => $json->Identification->TerminalId,
            'merchant_org_number'   => $json->Merchant->OrganizationNumber,
            'merchant_name'         => $json->Merchant->Name,
            'card_scheme'           => $json->Card->Scheme,
            'processed'             => new Carbon($json->Payment->ProcessedAtUtc),
            'amount_paid'           => $json->Payment->AmountPaid,
            'net_amount'            => $json->Payment->Details->NetAmount,
            'vat'                   => $json->Payment->Details->Vat,
            'vat_rate'              => $json->Payment->Details->VatRate,
            'transaction_reference' => strtoupper($json->TransactionReferences[0]->OperatorReference),
        ]);
    }

    protected function importOrcaTransaction(string $id, object $json): void
    {
        Transaction::create([
            'chunk_date'            => new Carbon($id),
            'line_id'               => $json->SalesPlace->LineId,
            'actor_id'              => $json->SalesPlace->ActorId,
            'operator_reference'    => strtoupper($json->Trip->OperatorReference),
            'tour_id'               => $json->Trip->TourId,
            'departure'             => new Carbon($json->Trip->Departure),
            'registered'            => $json->Trip->Registered,
            'stop_place_id_entry'   => $json->Trip->StopPlaceIdEntry,
            'stop_place_id_exit'    => $json->Trip->StopPlaceIdExit,
            'trailer'               => (int) $json->Trip->Trailer,
            'tariff_class'          => $json->Trip->TariffClass,
            'nation_lpn_front'      => $json->Trip->NationLpnFront,
            'ocr_confidence_front'  => $json->Trip->OcrConfidenceFront,
            'transaction_type'      => $json->TransactionType,
            'is_approved'           => (int) $json->Approval->IsApproved,
        ]);
    }

    protected function importStatistics(string $id, object $json): void
    {
        Statistics::create([
            'chunk_date'                => new Carbon($id),
            'actor_id'                  => $json->ActorId,
            'line_id'                   => $json->LineId,
            'tour_id'                   => $json->TourId,
            'operator_reference'        => strtoupper($json->OperatorReference),
            'departure'                 => new Carbon($json->Departure),
            'registered'                => $json->Registered,
            'stop_place_id_entry'       => $json->StopPlaceIdEntry ?? $json->Voyage->StopPlaceIdEntry,
            'stop_place_id_exit'        => $json->StopPlaceIdExit ?? $json->Voyage->StopPlaceIdExit,
            'statistic_name'            => $json->StatisticName ?? null,
            'statistic_count'           => $json->StatisticCount ?? null,
            'automatic_passenger_count' => $json->AutomaticPassengerCount ?? null,
            'manual_passenger_count'    => $json->ManualPassengerCount ?? null,
            'remaining_vehicle_count'   => $json->RemainingVehicleCount ?? null,
        ]);
    }

    /**
     * Get name of data type based on filename or path.
     *
     * @param string $filePath Filename or path
     *
     * @return string
     */
    protected function getSubjectFromFilePath(string $filePath): string
    {
        return match (true) {
            strpos($filePath, 'orca') !== false => 'OrcaTransaction',
            strpos($filePath, 'softpay') !== false => 'SoftpayTransaction',
            strpos($filePath, 'statistics') !== false => 'Statistics',
            default => '',
        };
    }

    /**
     * Add time offset +00:00 for GMT time if absent and appropriate.
     *
     * @param string $dateStr Date and time in UTC format.
     * @param string $localTime Local time in 24h format. No date.
     *
     * @return string
     */
    protected function addTimeOffset(string $dateStr, string $localTime): string
    {
        if (strpos($dateStr, '+') !== false) {
            // Time offset seems to be present. No conversion needed.
            return $dateStr;
        }
        // Input may actually be local time without an offset, and in this case
        // we can't reliably add a time offset.
        $dateStr = rtrim($dateStr, 'Z');
        $dateTime = explode('T', $dateStr);
        if (strncmp($dateTime[1], $localTime, 2) === 0) {
            // The hour values are identical, so it must be local time.
            return $dateStr;
        }
        // Add GMT offset.
        return sprintf('%s+00:00', $dateStr);
    }

    /**
     * Deleting imported transactions with the specified ID/date.
     *
     * @param string $id Chunk ID. Date on format YYYY-MM-DD
     *
     * @return $this
     */
    public function deleteImport(string $id)
    {
        $count = Transaction::whereDate('chunk_date', $id)->delete();
        $count += Statistics::whereDate('chunk_date', $id)->delete();
        $count += SoftpayTransaction::whereDate('chunk_date', $id)->delete();
        $this->debug('Deleted %d records with chunk date %s', $count, $id);
        return $this;
    }
}
