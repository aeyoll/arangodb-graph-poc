<?php

namespace triagens\ArangoDb;

require dirname(__FILE__) . DIRECTORY_SEPARATOR . 'vendor' . DIRECTORY_SEPARATOR . 'autoload.php';

$connectionOptions = array(
    // database name
    ConnectionOptions::OPTION_DATABASE      => '_system',
    // server endpoint to connect to
    ConnectionOptions::OPTION_ENDPOINT      => 'tcp://127.0.0.1:8529',
    // authorization type to use (currently supported: 'Basic')
    ConnectionOptions::OPTION_AUTH_TYPE     => 'Basic',
    // user for basic authorization
    ConnectionOptions::OPTION_AUTH_USER     => 'root',
    // password for basic authorization
    ConnectionOptions::OPTION_AUTH_PASSWD   => 'password',
    // connection persistence on server. can use either 'Close' (one-time connections)
    // or 'Keep-Alive' (re-used connections)
    ConnectionOptions::OPTION_CONNECTION    => 'Keep-Alive',
    // connect timeout in seconds
    ConnectionOptions::OPTION_TIMEOUT       => 3,
    // whether or not to reconnect when a keep-alive connection has timed out on server
    ConnectionOptions::OPTION_RECONNECT     => true,
    // optionally create new collections when inserting documents
    ConnectionOptions::OPTION_CREATE        => true,
    // optionally create new collections when inserting documents
    ConnectionOptions::OPTION_UPDATE_POLICY => UpdatePolicy::LAST,
);

try {
    // Setup connection, graph and graph handler
    $connection   = new Connection($connectionOptions);
    $graphHandler = new GraphHandler($connection);
    $edgeHandler  = new EdgeHandler($connection);
    $graph        = new Graph();
    $graph->set('_key', 'knows');
    $graph->addEdgeDefinition(new EdgeDefinition('knows', 'persons', 'persons'));
    // $graph->addEdgeDefinition(new EdgeDefinition('recommands', 'persons', 'doctors'));

    try {
        $graphHandler->dropGraph($graph);
    } catch (\Exception $e) {
        // graph may not yet exist. ignore this error for now
    }

    $graphHandler->createGraph($graph);

    // Define some arrays to build the content of the vertices and edges
    $aArray = array(
        "_key" => "alice",
        'name' => "Alice",
    );
    $bArray = array(
        "_key" => "bob",
        'name' => "Bob",
    );
    $cArray = array(
        "_key" => "charlie",
        'name' => "Charlie",
    );
    $dArray = array(
        "_key" => "dave",
        'name' => "Dave",
    );
    $eArray = array(
        "_key" => "eve",
        'name' => "Eve",
    );

    $a = Vertex::createFromArray($aArray);
    $b = Vertex::createFromArray($bArray);
    $c = Vertex::createFromArray($cArray);
    $d = Vertex::createFromArray($dArray);
    $e = Vertex::createFromArray($eArray);

    // Save the vertices
    $saveResult1 = $graphHandler->saveVertex('knows', $a, 'persons');
    $saveResult2 = $graphHandler->saveVertex('knows', $b, 'persons');
    $saveResult2 = $graphHandler->saveVertex('knows', $c, 'persons');
    $saveResult2 = $graphHandler->saveVertex('knows', $d, 'persons');
    $saveResult2 = $graphHandler->saveVertex('knows', $e, 'persons');

    // // Save the connecting edge
    $knows = Edge::createFromArray([]);
    // $saveEdgeResult1 = $graphHandler->saveEdge($graph, $a->getHandle(), $b->getHandle(), null, $knows);
    $edgeHandler->saveEdge('knows', $a->getHandle(), $b->getHandle(), $knows);

    $knows = Edge::createFromArray([]);
    // $saveEdgeResult2 = $graphHandler->saveEdge($graph, $b->getHandle(), $c->getHandle(), null, $knows);
    $edgeHandler->saveEdge('knows', $b->getHandle(), $c->getHandle(), $knows);

    $knows = Edge::createFromArray([]);
    // $saveEdgeResult3 = $graphHandler->saveEdge($graph, $b->getHandle(), $d->getHandle(), null, $knows);
    $edgeHandler->saveEdge('knows', $b->getHandle(), $d->getHandle(), $knows);

    $knows = Edge::createFromArray([]);
    // $saveEdgeResult4 = $graphHandler->saveEdge($graph, $e->getHandle(), $a->getHandle(), null, $knows);
    $edgeHandler->saveEdge('knows', $e->getHandle(), $a->getHandle(), $knows);

    $knows = Edge::createFromArray([]);
    // $saveEdgeResult5 = $graphHandler->saveEdge($graph, $e->getHandle(), $b->getHandle(), null, $knows);
    $edgeHandler->saveEdge('knows', $e->getHandle(), $b->getHandle(), $knows);

    // Get the vertices
    $getResult1 = $graphHandler->getVertex('knows', 'alice');
    $getResult2 = $graphHandler->getVertex('knows', 'bob');

    // check if vertex exists
    var_dump($graphHandler->hasVertex('knows', 'alice', [], 'persons'));

    // check if edge exists
    var_dump($graphHandler->hasEdge('knows', 'aknowsb'));

    // create a statement to insert 1000 test users
    $statement = new Statement($connection, array(
        'query' => 'FOR v, e, p IN 1..1 OUTBOUND \'persons/eve\' GRAPH \'knows\' RETURN v'
    ));

    $cursor = $statement->execute();
    var_dump($cursor->getAll());

    // // Get the connecting edge
    // $getEdgeResult1 = $graphHandler->getEdge('knows', 'aknowsb');

    // // Remove vertices and edges
    // $result1 = $graphHandler->removeVertex('knows', 'knows/aknowsb');
    // $result2 = $graphHandler->removeVertex('knows', 'knows/bknowsc');
    // $result3 = $graphHandler->removeVertex('knows', 'knows/bknowsd');
    // $result4 = $graphHandler->removeVertex('knows', 'knows/eknowsa');
    // $result5 = $graphHandler->removeVertex('knows', 'knows/eknowsb');

    // the connecting edge will be deleted automatically
} catch (ConnectException $e) {
    print $e . PHP_EOL;
} catch (ServerException $e) {
    print $e . PHP_EOL;
} catch (ClientException $e) {
    print $e . PHP_EOL;
}
