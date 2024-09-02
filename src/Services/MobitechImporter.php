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
        if (strpos($file, 'softpay') !== false) {
            $softpay = json_decode(file_get_contents($file));
            SoftpayTransaction::create([
                'chunk_date'            => new Carbon($id),
                'operator_reference'    => strtoupper($softpay->OperatorReference),
                'departure'             => $softpay->Departure,
                'line_id'               => $softpay->LineId,
                'tour_id'               => $softpay->TourId,
                'stop_place_id_entry'   => $softpay->StopPlaceIdEntry,
                'stop_place_id_exit'    => $softpay->StopPlaceIdExit,
                'actor_id'              => $softpay->ActorId,
                'country_code'          => $softpay->CountryCode,
                'transaction_number'    => $softpay->TransactionNumber,
                'trailer'               => $softpay->Trailer,
                'tariff_class'          => $softpay->TariffClass,
                'receipt_id'            => $softpay->Identification->ReceiptId,
                'batch_number'          => $softpay->Identification->BatchNumber,
                'terminal_id'           => $softpay->Identification->TerminalId,
                'merchant_org_number'   => $softpay->Merchant->OrganizationNumber,
                'merchant_name'         => $softpay->Merchant->Name,
                'card_scheme'           => $softpay->Card->Scheme,
                'processed'             => $this->addTimeOffset($softpay->Payment->ProcessedAtUtc, $softpay->Payment->LocalTime),
                'amount_paid'           => $softpay->Payment->AmountPaid,
                'net_amount'            => $softpay->Payment->Details->NetAmount,
                'vat'                   => $softpay->Payment->Details->Vat,
                'vat_rate'              => $softpay->Payment->Details->VatRate,
                'transaction_reference' => strtoupper($softpay->TransactionReferences[0]->OperatorReference),
            ]);
            return 1;
        }
        if (strpos($file, 'orca') !== false) {
            $transaction = json_decode(file_get_contents($file));
            Transaction::create([
                'chunk_date'            => new Carbon($id),
                'line_id'               => $transaction->SalesPlace->LineId,
                'actor_id'              => $transaction->SalesPlace->ActorId,
                'operator_reference'    => strtoupper($transaction->Trip->OperatorReference),
                'tour_id'               => $transaction->Trip->TourId,
                'departure'             => $transaction->Trip->Departure,
                'registered'            => $transaction->Trip->Registered,
                'stop_place_id_entry'   => $transaction->Trip->StopPlaceIdEntry,
                'stop_place_id_exit'    => $transaction->Trip->StopPlaceIdExit,
                'trailer'               => (int) $transaction->Trip->Trailer,
                'tariff_class'          => $transaction->Trip->TariffClass,
                'nation_lpn_front'      => $transaction->Trip->NationLpnFront,
                'ocr_confidence_front'  => $transaction->Trip->OcrConfidenceFront,
                'transaction_type'      => $transaction->TransactionType,
                'is_approved'           => (int) $transaction->Approval->IsApproved,
            ]);
            return 1;
        }
        if (strpos($file, 'statistics') !== false) {
            $statistics = json_decode(file_get_contents($file));
            Statistics::create([
                'chunk_date'                => new Carbon($id),
                'actor_id'                  => $statistics->ActorId,
                'line_id'                   => $statistics->LineId,
                'tour_id'                   => $statistics->TourId,
                'operator_reference'        => strtoupper($statistics->OperatorReference),
                'departure'                 => $statistics->Departure,
                'registered'                => $statistics->Registered,
                'stop_place_id_entry'       => $statistics->StopPlaceIdEntry ?? $statistics->Voyage->StopPlaceIdEntry,
                'stop_place_id_exit'        => $statistics->StopPlaceIdExit ?? $statistics->Voyage->StopPlaceIdExit,
                'statistic_name'            => $statistics->StatisticName ?? null,
                'statistic_count'           => $statistics->StatisticCount ?? null,
                'automatic_passenger_count' => $statistics->AutomaticPassengerCount ?? null,
                'manual_passenger_count'    => $statistics->ManualPassengerCount ?? null,
                'remaining_vehicle_count'   => $statistics->RemainingVehicleCount ?? null,
            ]);
            return 1;
        }
        return 0;
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
