<?php

namespace Ketwaroo\MinetestServerList;

use Ketwaroo\MinetestServerList\Storage\InterfaceStorage;

class App {

    public function __construct(
            protected array $config = [],
            protected ?InterfaceStorage $storage = null
    ) {

        if (
                !isset($this->storage)
                && isset($this->config['storage'])
                && class_exists($this->config['storage'])
        ) {
            $storageClass       = $this->config['storage'];
            $storageClassConfig = $this->config['storage_config'][$storageClass] ?? [];
            $this->storage      = new $storageClass(...$storageClassConfig);
        }

        if (!isset($this->storage)) {
            throw new \Exception('No storage. todo clean this up');
        }
        $this->storage->prune($config['PURGE_TIME'] ?? 350);

    }

    protected function jsonOut($data, $httpCode = 200) {
        header('HTTP/' . $httpCode, true);
        header('content-type: application/json');
        echo json_encode($data);

    }

    protected function sanitiseServerData(array $data) {

        $data['port'] = (int) ($data['port'] ?? 30000);

        if (isset($data['url']) && !preg_match('~^(https?\:)?//~', $data['url'])) {
            unset($data['url']);
        }

        $serverData["ip"] = $ip;

        if (isset($data['clients_list'])) {
            $data['clients']     = count($data['clients_list']);
            $data['clients_top'] = max($data['clients'], $data['clients_top'] ?? 0);
        }

        // @todo the rest of this.
        /*

          # Popularity
          if old:
          server["updates"] = old["updates"] + 1


          else:
          server["updates"] = 1
          server["total_clients"] = server["clients"]
          server["pop_v"] = server["total_clients"] / server["updates"]

          finishRequestAsync(server)

          return "Request has been filed.", 202
         */

        return $data;

    }

    public function handleAnnounce(array $request) {

        // @todo implement server banning
        // @todo cleanup old?

        if (!isset($request['json'])) {
            return $this->jsonOut('Missing json payload', 400);
        }

        $serverData = json_decode($request['json'], true);

        if (!is_array($serverData)) {
            return $this->jsonOut('JSON data is not an object.', 400);
        }

        if (!isset($serverData['action'])) {
            return $this->jsonOut('Missing action.', 400);
        }

        $ip         = $_SERVER['REMOTE_ADDR'];
        $serverData = $this->sanitiseServerData($serverData);
        $port       = $serverData['port'];
        $old        = $this->storage->get($ip, $port);

        switch ($serverData['action']) {
            case 'start':
                $serverData["uptime"] = 0;

            case 'update':

                if (empty($old) && empty($this->config["ALLOW_UPDATE_WITHOUT_OLD"])) {
                    return $this->jsonOut('Server to update not found.', 404);
                }

                if (empty($old)) {
                    $serverData['updates'] = 1;

                    $serverData["total_clients"] = $serverData["clients"];
                    $serverData["pop_v"]         = $serverData["total_clients"] / $serverData["updates"];
                }
                else {
                    # Make sure that startup options are saved
                    foreach (["dedicated", "rollback", "mapgen", "privs", "can_see_far_names", "mods"] as $oldField) {
                        if (isset($old[$oldField])) {
                            $serverData[$oldField] = $old[$oldField];
                        }
                    }

                    $serverData['updates'] = ($old['updates'] ?? 1) + 1;

                    # This is actually a count of all the client numbers we've received,
                    # it includes clients that were on in the previous update.
                    $serverData["total_clients"] = $old["total_clients"] + $serverData["clients"];
                }
                $serverData["update_time"] = time();
                $serverData['ip']          = $ip;
                $serverData['address']     = $serverData['address'] ?: $ip;
                $serverData['port']        = $port;

                $this->storage->add($ip, $port, $serverData);
                return $this->jsonOut("Request has been filed.", 202);
                break;
            case 'delete':
                if (empty($old)) {
                    return $this->jsonOut('Server not found.', 404);
                }

                $this->storage->del($ip, $port);
                return $this->jsonOut("Removed from server list.");

                break;

            default:
                return $this->jsonOut('Invalid action field.', 400);
        }

    }

    public function handleList() {
        return $this->jsonOut($this->storage->getAll());

    }

    public function handleGeoip() {
        // not implemented.
        return $this->jsonOut([
                    "continent" => "NA",
        ]);

    }

}
