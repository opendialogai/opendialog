<?php

namespace App\Http\Controllers;

use App\User;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redis;

class StatusController extends Controller
{
    public function handle()
    {
        $checkUsersExist = request()->get('check_db_user') === "true";

        $dbStatus = $this->checkDbStatus($checkUsersExist);
        $dgraphStatus = $this->checkDgraphStatus();
        $redisStatus = $this->checkRedisStatus();

        $response = [
            'db' => ($dbStatus === true) ? 'OK' : $dbStatus,
            'dgraph' => ($dgraphStatus === true) ? 'OK' : $dgraphStatus,
            'redis' => ($redisStatus === true) ? 'OK' : $redisStatus,
        ];

        $errorCode = intval(request()->get('error_code')) ? intval(request()->get('error_code')) : 200;
        $returnCode = $dbStatus === true && $dgraphStatus === true && $redisStatus === true ? 200 : $errorCode;

        return response($response, $returnCode);
    }

    /**
     * @param boolean $checkUsersExist
     * @return string|bool
     */
    private function checkDbStatus(bool $checkUsersExist)
    {
        try {
            DB::connection()->getPdo();
            if ($checkUsersExist) {
                return $this->checkUserExists();
            }
        } catch (\Exception $e) {
            return $e->getMessage();
        }

        return true;
    }

    /**
     * @return bool|string
     */
    private function checkUserExists()
    {
        try {
            return User::count() > 0 ? true : "No users exist in DB";
        } catch (\Exception $e) {
            return $e->getMessage();
        }
    }

    /**
     * @return string|bool
     */
    private function checkDgraphStatus()
    {
        $dgraphUrl = config('opendialog.graphql.DGRAPH_BASE_URL');
        $dGraphPort = config('opendialog.graphql.DGRAPH_PORT');

        $client = new Client([
            'base_uri' => $dgraphUrl . ":" . $dGraphPort
        ]);

        try {
            $response = $client->request('GET', '/health')->getBody()->getContents();
            $status = json_decode($response)[0]->status;

            return $status;
        } catch (\Exception $e) {
            return $e->getMessage();
        }
    }

    public static function checkRedisStatus()
    {
        $isReady = true;
        try {
            $redis = Redis::connection();
            $redis->connect();
            $redis->disconnect();
        } catch (\Exception $e) {
            $isReady = $e->getMessage();
        }

        return $isReady;
    }
}
