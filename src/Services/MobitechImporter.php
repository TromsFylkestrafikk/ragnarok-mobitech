<?php

namespace Ragnarok\Mobitech\Services;

use Illuminate\Support\Carbon;
use Ragnarok\Mobitech\Models\Transaction;
use Ragnarok\Sink\Traits\LogPrintf;

class MobitechImporter
{
    use LogPrintf;

    public function __construct()
    {
        $this->logPrintfInit('[MobitechImporter]: ');
    }

    public function import(string $id, string $file)
    {
        $transaction = json_decode(file_get_contents($file));
        Transaction::create([
            'chunk_date'            => new Carbon($id),
            'obu_issuer_id'         => $transaction->Obu->ObuIssuerId,
            'line_id'               => $transaction->SalesPlace->LineId,
            'actor_id'              => $transaction->SalesPlace->ActorId,
            'lane'                  => $transaction->SalesPlace->Lane,
            'device_type'           => $transaction->SalesPlace->DeviceType,
            'device_id'             => $transaction->SalesPlace->DeviceId,
            'validation_file'       => $transaction->SalesPlace->ValidationFile,
            'operator_reference'    => $transaction->Trip->OperatorReference,
            'tour_id'               => $transaction->Trip->TourId,
            'departure'             => $transaction->Trip->Departure,
            'registered'            => $transaction->Trip->Registered,
            'stop_place_id_entry'   => $transaction->Trip->StopPlaceIdEntry,
            'stop_place_id_exit'    => $transaction->Trip->StopPlaceIdExit,
            'trailer'               => (int) $transaction->Trip->Trailer,
            'signal_code'           => $transaction->Trip->SignalCode,
            'measured_length'       => $transaction->Trip->MeasuredLength,
            'tariff_class'          => $transaction->Trip->TariffClass,
            'nation_lpn_front'      => $transaction->Trip->NationLpnFront,
            'ocr_confidence_front'  => $transaction->Trip->OcrConfidenceFront,
            'seq_lc'                => $transaction->Trip->SeqLc,
            'seq_video'             => $transaction->Trip->SeqVideo,
            'transaction_type'      => $transaction->TransactionType,
            'app_version'           => $transaction->AppVersion,
            'netex_id'              => $transaction->NetexId,
            'is_approved'           => (int) $transaction->Approval->IsApproved,
        ]);
        return $this;
    }

    public function deleteImport(string $id)
    {
        $count = Transaction::whereDate('chunk_date', $id)->delete();
        $this->debug('Deleted %d records with chunk date %s', $count, $id);
        return $this;
    }
}
