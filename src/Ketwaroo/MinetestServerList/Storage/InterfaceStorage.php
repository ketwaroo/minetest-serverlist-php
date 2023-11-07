<?php

namespace Ketwaroo\MinetestServerList\Storage;

interface InterfaceStorage {

    /**
     * Get full list
     * @return array
     */
    public function getAll(): array;
    /**
     * 
     * @param type $data
     * @return static
     */
    public function add(string $ip, int $port, array $data): static;
    public function get($ip, int $port = 30000): ?array;
    public function del($ip, int $port = 30000):static;
    public function prune(int $maxAge):static;

}
