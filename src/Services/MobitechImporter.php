<?php

namespace Ragnarok\Mobitech\Services;

use Illuminate\Support\Carbon;
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
     * @return $this
     */
    public function import(string $id, string $file)
    {
        if (strpos($file, 'softpay') !== false) {
            // Ignoring these transactions for now.
            return $this;
        }
        if (strpos($file, 'statistics') === false) {
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
            return $this;
        }
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
        return $this;
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
        $this->debug('Deleted %d records with chunk date %s', $count, $id);
        return $this;
    }
}
