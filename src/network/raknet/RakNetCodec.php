<?php

class RakNetCodec{
	private static function encodeIPAddress(RakNetPacket $packet) {
		$packet->buffer .= chr(4);
		$packet->buffer .= Utils::writeInt($packet->ip);
		$packet->buffer .= Utils::writeShort($packet->port);
	}

	public static function encode(RakNetPacket $packet){
		if($packet->buffer != null && strlen($packet->buffer) > 0){
			return;
		}
		$packet->buffer .= chr($packet->pid());

		switch($packet->pid()){
			case RakNetInfo::OPEN_CONNECTION_REPLY_1:
				$packet->buffer .= RakNetInfo::MAGIC;
				$packet->buffer .= Utils::writeLong($packet->serverID);
				$packet->buffer .= chr(0); //server security
				$packet->buffer .= Utils::writeShort($packet->mtuSize);
				break;
			case RakNetInfo::OPEN_CONNECTION_REPLY_2:
				$packet->buffer .= RakNetInfo::MAGIC;
				$packet->buffer .= Utils::writeLong($packet->serverID);
				static::encodeIPAddress($packet);
				$packet->buffer .= Utils::writeShort($packet->mtuSize);
				$packet->buffer .= chr(0); //Server security
				break;
			case RakNetInfo::INCOMPATIBLE_PROTOCOL_VERSION:
				$packet->buffer .= chr(RakNetInfo::STRUCTURE);
				$packet->buffer .= RakNetInfo::MAGIC;
				$packet->buffer .= Utils::writeLong($packet->serverID);
				break;
			case RakNetInfo::UNCONNECTED_PONG:
			case RakNetInfo::ADVERTISE_SYSTEM:
				$packet->buffer .= Utils::writeLong($packet->pingID);
				$packet->buffer .= Utils::writeLong($packet->serverID);
				$packet->buffer .= RakNetInfo::MAGIC;
				
				$packet->buffer .= Utils::writeShort(strlen($packet->serverType));
				$packet->buffer .= $packet->serverType;
				break;
			case RakNetInfo::DATA_PACKET_0:
			case RakNetInfo::DATA_PACKET_1:
			case RakNetInfo::DATA_PACKET_2:
			case RakNetInfo::DATA_PACKET_3:
			case RakNetInfo::DATA_PACKET_4:
			case RakNetInfo::DATA_PACKET_5:
			case RakNetInfo::DATA_PACKET_6:
			case RakNetInfo::DATA_PACKET_7:
			case RakNetInfo::DATA_PACKET_8:
			case RakNetInfo::DATA_PACKET_9:
			case RakNetInfo::DATA_PACKET_A:
			case RakNetInfo::DATA_PACKET_B:
			case RakNetInfo::DATA_PACKET_C:
			case RakNetInfo::DATA_PACKET_D:
			case RakNetInfo::DATA_PACKET_E:
			case RakNetInfo::DATA_PACKET_F:
				$packet->buffer .= Utils::writeLTriad($packet->seqNumber);
				foreach($packet->data as $pk){
					static::encodeDataPacket($packet, $pk);
				}
				break;
			case RakNetInfo::NACK:
			case RakNetInfo::ACK:
				$payload = b"";
				$records = 0;
				$pointer = 0;
				sort($packet->packets, SORT_NUMERIC);
				$max = count($packet->packets);

				while($pointer < $max){
					$type = true;
					$curr = $start = $packet->packets[$pointer];
					for($i = $start + 1; $i < $max; ++$i){
						$n = $packet->packets[$i];
						if(($n - $curr) === 1){
							$curr = $end = $n;
							$type = false;
							$pointer = $i + 1;
						}else{
							break;
						}
					}
					++$pointer;
					if($type === false){
						$payload .= "\x00";
						$payload .= Utils::writeLTriad($start);
						$payload .= Utils::writeLTriad($end);
					}else{
						$payload .= Utils::writeBool(true);
						$payload .= Utils::writeLTriad($start);
					}
					++$records;
				}
				$packet->buffer .= Utils::writeShort($records);
				$packet->buffer .= $payload;
				break;
			default:

		}
	}

	public static function encodeDataPacket(RakNetPacket $packet, RakNetDataPacket $pk){
		$packet->buffer .= chr(($pk->reliability << 5) | ($pk->hasSplit > 0 ? 0b00010000 : 0));
		$packet->buffer .= Utils::writeShort(strlen($pk->buffer) << 3);
		if($pk->reliability === RakNetInfo::RELIABILITY_RELIABLE
			or $pk->reliability === RakNetInfo::RELIABILITY_RELIABLE_ORDERED
			or $pk->reliability === RakNetInfo::RELIABILITY_RELIABLE_SEQUENCED
			or $pk->reliability === RakNetInfo::RELIABILITY_RELIABLE_WITHACKRECEIPT
			or $pk->reliability === RakNetInfo::RELIABILITY_RELIABLE_ORDERED_WITHACKRECEIPT){
			$packet->buffer .= Utils::writeLTriad($pk->messageIndex);
		}
		if($pk->reliability == RakNetInfo::RELIABILITY_UNRELIABLE_SEQUENCED || $pk->reliability == RakNetInfo::RELIABILITY_RELIABLE_SEQUENCED){
			$packet->buffer .= Utils::writeLTriad($pk->seqIndex);
		}

		if($pk->reliability === RakNetInfo::RELIABILITY_UNRELIABLE_SEQUENCED
			or $pk->reliability === RakNetInfo::RELIABILITY_RELIABLE_ORDERED
			or $pk->reliability === RakNetInfo::RELIABILITY_RELIABLE_SEQUENCED
			or $pk->reliability === RakNetInfo::RELIABILITY_RELIABLE_ORDERED_WITHACKRECEIPT){
			$packet->buffer .= Utils::writeLTriad($pk->orderIndex);
			$packet->buffer .= chr($pk->orderChannel);
		}

		if($pk->hasSplit === true){
			$packet->buffer .= Utils::writeInt($pk->splitCount);
			$packet->buffer .= Utils::writeShort($pk->splitID);
			$packet->buffer .= Utils::writeInt($pk->splitIndex);
		}

		$packet->buffer .= $pk->buffer;
	}
	
	/**
	 * @deprecated
	 */
	public $packet;
	/**
	 * @deprecated
	 */
	public $buffer;
	/**
	 * @deprecated use RakNetCodec::encode
	 * @param RakNetPacket $packet
	 */
	public function __construct(RakNetPacket $packet){
		$this->packet = $packet;
		$this->buffer = &$packet->buffer;
		static::encode($packet);
	}
	
}