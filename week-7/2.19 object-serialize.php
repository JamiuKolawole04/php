<?php

echo "Object serialization";

echo serialize(1);

echo "<br />";
echo serialize("Hello");


echo "<br />";


echo unserialize(serialize(1));

echo "<br />";
echo unserialize(serialize("Hello"));