<?php

/*
 * Click nbfs://nbhost/SystemFileSystem/Templates/Licenses/license-default.txt to change this license
 * Click nbfs://nbhost/SystemFileSystem/Templates/Scripting/PHPClass.php to edit this template
 */

namespace Ketwaroo\MinetestServerList\Storage;

/**
 * Description of PlainJsonStorage
 *
 * @author null
 */
class PlainJsonStorage implements InterfaceStorage {

    protected array $data = [];

    public function __construct(
            protected string $dataFile
    ) {
        if (is_file($dataFile)) {
            $this->data = json_decode(file_get_contents($dataFile), true);
        }

    }

    public function __destruct() {

        // recalculate totals

        if (empty($this->data['list'])) {
            $this->data['total']     = ['servers' => 0, 'clients' => 0,];
            $this->data['total_max'] = ['servers' => 0, 'clients' => 0,];
            $this->data['list']      = [];
        }
        else {
            
        }

        file_put_contents($this->dataFile, json_encode($this->data, JSON_PRETTY_PRINT));

    }

    public function add(string $ip, int $port, array $data): static {
        $this->data["list"]["{$ip}|{$port}"] = $data;
        return $this;

    }

    public function get($ip, int $port = 30000): ?array {
        return $this->data["list"]["{$ip}|{$port}"] ?? null;

    }

    public function del($ip, int $port = 30000): static {
        unset($this->data["list"]["{$ip}|{$port}"]);
        return $this;

    }

    public function getAll(): array {
        $tmp = $this->data;

        $tmp['list'] = isset($this->data['list']) ? array_values($this->data['list']) : [];
        return $tmp;

    }

    public function prune(int $maxAge): static {
        $now = time();
        foreach ($this->data['list'] ?? [] as $k => $v) {
            if ((($v['update_time'] ?? 0) + $maxAge) < $now) {
                $this->del($v['ip'], $v['port']);
            }
        }

        return $this;

    }

}
