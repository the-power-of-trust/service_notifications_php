<?php

// Test what Mongo Extensions are installed
if (class_exists('\MongoClient')) {
    echo "Old Mongo extension is installed (\MongoClient)\n";
} else {
    echo "Old Mongo extension is NOT installed (\MongoClient)\n";
}

if (class_exists('\MongoDB\Driver\Manager')) {
    echo "New Mongo extension is installed (\MongoDB\Driver\Manager)\n";
} else {
    echo "New Mongo extension is NOT installed (\MongoDB\Driver\Manager)\n";
}