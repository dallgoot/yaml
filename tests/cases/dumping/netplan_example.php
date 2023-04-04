<?php

use Dallgoot\Yaml\Types\YamlObject;
use Dallgoot\Yaml\Types\Compact;


$yaml = new YamlObject(0);

$network = (object) ['ethernets' => new \StdClass, 'version' => 2];



$enp0s3 = (object) [
  'addresses' => new Compact(['192.168.1.84/24']),
  'gateway4' => '192.168.1.1',
  'nameservers' => new \StdClass
];


$enp0s3->nameservers->addresses = new Compact(['192.168.1.1']);
$network->ethernets->enp0s3 = $enp0s3;

$yaml->network = $network;


return $yaml;
