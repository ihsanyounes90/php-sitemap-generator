<?php

namespace Yo;


class ApiCrawler
{
    /**
     * @var RequestHelper
     */
    private $client;
    /**
     * @var array configurations
     */
    private $conf;
    /**
     * @var SitemapGenerator
     */
    private $sitemap;

    public function __construct($baseURI, $headers, $conf, $sitemap)
    {
        $this->client = new RequestHelper($baseURI, $headers);
        $this->conf = $conf;
        $this->sitemap = $sitemap;
    }

    public function iterate($data, $on, $printParams, $use, $useParams)
    {
        $items = $data;

        if ($on != "_root") {
            $items = $data[$on];
        }

        foreach ($items as $item) {
            $this->printdata($item, $printParams);

            if ($use) {
                $preparedParams = [];
                if ($useParams) {
                    foreach ($useParams as $param) {
                        $preparedParams[] = $item[$param];
                    }
                }
                $this->scrape($use, $preparedParams);
            }
        }
    }

    public function printdata($item, $printParams)
    {
        $url = $this->traverseData($item, $printParams["url"]);
        if($url){
            $this->sitemap->addUrl($url, null, null, $printParams["priority"]);
        }
    }

    public function scrape($name, $params = null)
    {
        $source = $this->conf[$name];
        $path = $source["path"];
        if ($path) {
            $path = $this->format($path, $params);
        }

        echo "fetching: ", $path, "\n";

        $data = $this->client->get($path);
        if ($source["iterate"]) {
            $this->iterate($data,
                $source["iterate"]["obj"],
                $source["print"],
                $source["iterate"]["use"] ?? null,
                $source["iterate"]["params"] ?? null);
        }
    }

    private function format($msg, $vars)
    {
        $vars = (array)$vars;

        $msg = preg_replace_callback('#\{\}#', function ($r) {
            static $i = 0;
            return '{' . ($i++) . '}';
        }, $msg);

        return str_replace(
            array_map(function ($k) {
                return '{' . $k . '}';
            }, array_keys($vars)),

            array_values($vars),

            $msg
        );
    }

    private function traverseData($data, $path)
    {
        $result = $data;
        foreach (explode(".", $path) as $p) {
            $result = $result[$p];
        }
        return $result;
    }


}
