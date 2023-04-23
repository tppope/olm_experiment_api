<?php

namespace App\GraphQL\Mutations;

use App\Models\Device;
use Symfony\Component\Process\Process;

class StartVideoStream
{
    /**
     * @param null $_
     * @param array<string, mixed> $args
     */
    public function __invoke($_, array $args)
    {
        $device = Device::query()->findOrFail($args['deviceID']);

        if (!$device->camera_port) {
            return ["isRunning" => false, "status" => "Camera port is NOT set"];
        }

        $EXIT_ON_NO_CLIENTS_SEC = config('video-stream.exit_on_no_clients_sec');

        $process = Process::fromShellCommandline("~/ustreamer/ustreamer --device=$device->camera_port --host=0.0.0.0 --port=9001 --process-name-prefix='ustreamer-$device->name' --exit-on-no-clients=$EXIT_ON_NO_CLIENTS_SEC --allow-origin=\*");

        $process->start();
        sleep(1);

        if (!$process->isStarted()) {
            return ["isRunning" => $process->isStarted(), "status" => $process->getErrorOutput()];

        }

        return ["isRunning" => $process->isStarted(), "status" => "Video stream is running"];

    }
}
