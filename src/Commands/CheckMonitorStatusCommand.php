<?php

namespace CleaniqueCoders\AppPulse\Commands;

use CleaniqueCoders\AppPulse\Actions\MonitorHistory as MonitorHistoryAction;
use CleaniqueCoders\AppPulse\Models\Monitor;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;

class CheckMonitorStatusCommand extends Command
{
    protected $signature = 'monitor:check-status';

    protected $description = 'Check the status and SSL validity of all monitors';

    public function handle()
    {
        $monitors = Monitor::all();

        foreach ($monitors as $monitor) {
            $this->checkMonitor($monitor);
        }

        $this->info('Monitor status check completed.');
    }

    protected function checkMonitor(Monitor $monitor)
    {
        try {
            $startTime = microtime(true);
            $response = Http::get($monitor->url);
            $status = $response->ok() ? 'up' : 'down';
            $responseTime = (microtime(true) - $startTime) * 1000;

            MonitorHistoryAction::create([
                'monitor_id' => $monitor->id,
                'type' => 'uptime',
                'status' => $status,
                'response_time' => $responseTime,
            ]);

            $this->info("Monitor {$monitor->url} is {$status}.");

            if ($monitor->ssl_check) {
                $this->checkSsl($monitor);
            }
        } catch (\Exception $e) {
            MonitorHistoryAction::create([
                'monitor_id' => $monitor->id,
                'type' => 'uptime',
                'status' => 'down',
                'error_message' => $e->getMessage(),
            ]);

            $this->error("Failed to check monitor {$monitor->url}: {$e->getMessage()}");
        }
    }

    protected function checkSsl(Monitor $monitor)
    {
        $host = parse_url($monitor->url, PHP_URL_HOST);
        $streamContext = stream_context_create(['ssl' => ['capture_peer_cert' => true]]);

        $client = @stream_socket_client(
            "ssl://{$host}:443",
            $errno, $errstr, 30, STREAM_CLIENT_CONNECT, $streamContext
        );

        if (! $client) {
            MonitorHistory::create([
                'monitor_id' => $monitor->id,
                'type' => 'ssl',
                'status' => 'ssl_expired',
                'error_message' => 'Unable to connect for SSL check.',
            ]);
            $this->error("Failed to connect to {$monitor->url} for SSL check.");

            return;
        }

        $cont = stream_context_get_params($client);
        $cert = openssl_x509_parse($cont['options']['ssl']['peer_certificate'] ?? []);

        if (! $cert || ! isset($cert['validTo'])) {
            MonitorHistoryAction::create([
                'monitor_id' => $monitor->id,
                'type' => 'ssl',
                'status' => 'ssl_expired',
                'error_message' => 'SSL certificate not available or invalid.',
            ]);
            $this->error("SSL certificate not available or invalid for {$monitor->url}.");

            return;
        }

        $validTo = date_create_from_format('ymdHise', $cert['validTo'].'Z');

        if (! $validTo) {
            MonitorHistoryAction::create([
                'monitor_id' => $monitor->id,
                'type' => 'ssl',
                'status' => 'ssl_expired',
                'error_message' => 'Failed to parse SSL expiration date.',
            ]);
            $this->error("Failed to parse SSL expiration date for {$monitor->url}.");

            return;
        }

        $daysLeft = $validTo->diff(now())->days;
        $status = $daysLeft > 0 ? 'ssl_valid' : 'ssl_expired';

        MonitorHistoryAction::create([
            'monitor_id' => $monitor->id,
            'type' => 'ssl',
            'status' => $status,
            'response_time' => $daysLeft,
        ]);

        $this->info("SSL for {$monitor->url} is {$status} with {$daysLeft} days remaining.");
    }
}
