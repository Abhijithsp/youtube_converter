<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Artisan;
use App\Models\Download;

class QueueController extends Controller
{
    public function status()
    {
        $pending = DB::table('jobs')->count();
        $failed = DB::table('failed_jobs')->count();
        $processing = Download::where('status', 'processing')->count();

        return response()->json([
            'pending' => $pending,
            'processing' => $processing,
            'failed' => $failed,
        ]);
    }

    public function jobs()
    {
        $jobs = DB::table('jobs')
            ->orderBy('id', 'asc')
            ->get()
            ->map(function ($job) {
                $payload = json_decode($job->payload, true);
                $downloadInfo = null;
                
                // Keep it safe, avoid full unserialize if possible or handle errors
                try {
                     $commandObj = unserialize($payload['data']['command']);
                     if (isset($commandObj->download) && $commandObj->download instanceof Download) {
                         $downloadInfo = [
                             'id' => $commandObj->download->id,
                             'title' => $commandObj->download->title,
                             'thumbnail_url' => $commandObj->download->thumbnail_url,
                         ];
                     }
                } catch (\Exception $e) {
                    // Ignore unserialize errors
                }

                return [
                    'id' => $job->id,
                    'queue' => $job->queue,
                    'attempts' => $job->attempts,
                    'reserved_at' => $job->reserved_at,
                    'created_at' => $job->created_at,
                    'download' => $downloadInfo
                ];
            });

        return response()->json($jobs);
    }
    
    public function failedJobs()
    {
        $jobs = DB::table('failed_jobs')
            ->orderBy('failed_at', 'desc')
            ->get()
            ->map(function ($job) {
                $payload = json_decode($job->payload, true);
                $downloadInfo = null;
                
                try {
                     $commandObj = unserialize($payload['data']['command']);
                     if (isset($commandObj->download) && $commandObj->download instanceof Download) {
                         $downloadInfo = [
                             'id' => $commandObj->download->id,
                             'title' => $commandObj->download->title,
                         ];
                     }
                } catch (\Exception $e) {
                }

                return [
                    'id' => $job->id,
                    'connection' => $job->connection,
                    'queue' => $job->queue,
                    'exception' => $job->exception,
                    'failed_at' => $job->failed_at,
                    'download' => $downloadInfo
                ];
            });
            
        return response()->json($jobs);
    }

    public function retryAllFailed()
    {
        Artisan::call('queue:retry', ['id' => 'all']);
        return response()->json(['message' => 'All failed jobs retried']);
    }

    public function retryJob($id)
    {
        // The id param for queue:retry works on the failed_jobs UUID or ID.
        Artisan::call('queue:retry', ['id' => $id]);
        return response()->json(['message' => 'Job retried']);
    }

    public function clearFailed()
    {
        Artisan::call('queue:flush');
        return response()->json(['message' => 'Failed jobs cleared']);
    }

    public function cancelJob($id)
    {
        $deleted = DB::table('jobs')->where('id', $id)->delete();
        
        if ($deleted) {
            return response()->json(['message' => 'Job cancelled']);
        }
        
        return response()->json(['message' => 'Job not found'], 404);
    }
}
