<?php

class MinecraftInterface{

	public $bandwidth;
	private $socket;
	private $packets;
	public $start;
	function __construct($server, $port = 25565, $serverip = "0.0.0.0"){
		$this->socket = new UDPSocket($server, $port, true, $serverip);
		if($this->socket->connected === false){
			console("[SEVERE] Couldn't bind to $serverip:" . $port, true, true, 0);
			exit(1);
		}
		$this->bandwidth = [0, 0, microtime(true)];
		$this->start = microtime(true);
		$this->packets = [];
	}

	public function close(){
		return $this->socket->close(false);
	}

	public function readPacket(){
		if($this->socket->connected === false){
			return false;
		}
		$buf = "";
		$source = false;
		$port = 1;
		$len = $this->socket->read($buf, $source, $port);
		if($len === false or $len === 0){
			return false;
		}
		$this->bandwidth[0] += $len;
		ServerAPI::request()->api->dhandle("mcinterface.read", ["buffer" => $buf, "source" => $source, "port" => $port]);
		return $this->parsePacket($buf, $source, $port);
	}

	private function parsePacket($buffer, $source, $port){
		$pid = ord($buffer[0]);

		if(RakNetInfo::isValid($pid)){
			$packet = RakNetParser::parse($buffer);
			if($packet !== false){
				$packet->ip = $source;
				$packet->port = $port;
				if(EventHandler::callEvent(new PacketReceiveEvent($packet)) === BaseEvent::DENY){
					return false;
				}
				return $packet;
			}
			return false;
		}elseif($pid === 0xfe and $buffer[1] === "\xfd" and ServerAPI::request()->api->query instanceof QueryHandler){
			$packet = new QueryPacket;
			$packet->ip = $source;
			$packet->port = $port;
			$packet->buffer =& $buffer;
			if(EventHandler::callEvent(new PacketReceiveEvent($packet)) === BaseEvent::DENY){
				return false;
			}
			ServerAPI::request()->api->query->handle($packet);
		}else{
			$packet = new Packet();
			$packet->ip = $source;
			$packet->port = $port;
			$packet->buffer =& $buffer;
			EventHandler::callEvent(new PacketReceiveEvent($packet));
			return false;
		}
	}

	public function writePacket(Packet $packet){
		if(EventHandler::callEvent(new PacketSendEvent($packet)) === BaseEvent::DENY){
			return 0;
		}elseif($packet instanceof RakNetPacket){
			RakNetCodec::encode($packet);
		}
		$write = $this->socket->write($packet->buffer, $packet->ip, $packet->port);
		$this->bandwidth[1] += $write;
		return $write;
	}
}