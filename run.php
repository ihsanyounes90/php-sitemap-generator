<?php

require __DIR__ . '/vendor/autoload.php';

$yml = yaml_parse_file("conf.yml");
$sitemapGenerator = new \Yo\SitemapGenerator("http://www.musement.com", "/tmp/yo_es-ES.xml");

$crawler = new \Yo\ApiCrawler("https://api.musement.com/", ["Accept" => "application/json", "Accept-Language" => "es-ES"], $yml, $sitemapGenerator);

$crawler->scrape("city");

$sitemapGenerator->createSitemap();

$sitemapGenerator->writeSitemap();

// Save to s3
echo "Saving to s3.. \n";

$s3 = new Aws\S3\S3Client([
    'region'  => 'eu-west-1',
    'version' => 'latest',
    'credentials' => [
        'key'    => "--key--",
        'secret' => "--secret--",
    ]
]);

$result = $s3->putObject([
    'Bucket' => 'test-candidati',
    'Key'    => "yo_es-ES.xml",
    'Body'   => file_get_contents('/tmp/yo_es-ES.xmlsitemap.xml')
]);

// Print the body of the result by indexing into the result object.
var_dump($result);