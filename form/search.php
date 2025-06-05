<?php

$input_description = "J'ai un problème de téléphone";
$model_file = "resultat.json";
$command = escapeshellcmd("python predict.py \"$input_description\" \"$model_file\"");
$output = shell_exec($command);

echo "La catégorie prédite pour \"$input_description\" est : $output";
?>
