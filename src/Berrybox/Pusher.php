<?php
namespace Berrybox;
use Ratchet\ConnectionInterface;
use Ratchet\Wamp\WampServerInterface;

class Pusher implements WampServerInterface{
	protected $subscribedTopics = array();

	public function onSubscribe(ConnectionInterface $conn, $topic){
		$this->subscribedTopics[$topic->getId()] = $topic;
		echo "New subscription ({$conn->resourceId}) to topic {$topic}\n";
	}

	public function onUnSubscribe(ConnectionInterface $conn, $topic){

	}

	public function onOpen(ConnectionInterface $conn){
		echo "New connection ({$conn->resourceId})\n";
	}

	public function onClose(ConnectionInterface $conn){
		echo "Connection closed ({$conn->resourceId})\n";
	}

	public function onCall(ConnectionInterface $conn, $id, $topic, array $params){

	}

	public function onPublish(ConnectionInterface $conn, $topic, $event, array $exclude, array $eligible){
		echo "{$conn->resourceId} has published to the topic {$topic}";
		print_r($event);

		if(!array_key_exists($event['token'], $this->subscribedTopics)){
			return;
		}

		$topic = $this->subscribedTopics[$event['token']];

		$topic->broadcast($event);
	}

	public function onEntry($entry){
		$entryData = json_decode($entry, true);
		echo "published\n";

		if(!array_key_exists($entryData['token'], $this->subscribedTopics)){
			return;
		}

		$topic = $this->subscribedTopics[$entryData['token']];

		$topic->broadcast($entryData);
	}

	public function onError(ConnectionInterface $conn, \Exception $e){
		echo "Error {$e->getMessage()}\n";
	}
}
