<?php

namespace Tlconseil\BlogBundle\Service;

class CountShares
{
    /**
     * @param $url
     * @return int
     */
    private function getCountTwitter($url)
    {
        $json_string = file_get_contents(sprintf('http://urls.api.twitter.com/1/urls/count.json?url=%s', $url));
        $json = json_decode($json_string, true);
        if (!empty($json['count']))
            return $json['count'];
        return 0;
    }

    /**
     * @param $url
     * @return int
     */
    private function getCountLinkedIn($url)
    {
        $json_string = file_get_contents(sprintf('http://www.linkedin.com/countserv/count/share?url=%s&format=json', $url));
        $json = json_decode($json_string, true);
        if (!empty($json['count']))
            return $json['count'];
        return 0;
    }

    /**
     * @param $url
     * @return int
     */
    private function getCountFacebook($url)
    {
        $json_string = file_get_contents(sprintf('http://graph.facebook.com/?ids=%s', $url));
        $json = json_decode($json_string, true);
        if (!empty($json[$url]['shares']))
            return $json[$url]['shares'];
        return 0;
    }

    /**
     * @param $url
     * @return int
     */
    private function getCountGoogle($url)
    {
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, "https://clients6.google.com/rpc");
        curl_setopt($curl, CURLOPT_POST, 1);
        curl_setopt($curl, CURLOPT_POSTFIELDS, '[{"method":"pos.plusones.get","id":"p","params":{"nolog":true,"id":"' . $url . '","source":"widget","userId":"@viewer","groupId":"@self"},"jsonrpc":"2.0","key":"p","apiVersion":"v1"}]');
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_HTTPHEADER, array('Content-type: application/json'));
        $curl_results = curl_exec ($curl);
        curl_close ($curl);
        $json = json_decode($curl_results, true);
        if (!empty($json[0]['result']['metadata']['globalCounts']['count']))
            return $json[0]['result']['metadata']['globalCounts']['count'];
        return 0;
    }

    /**
     * @param $url
     * @return int
     */
    public function getCount($url)
    {
        $availableSocialNetworks = array('Twitter', 'LinkedIn', 'Facebook', 'Google');
        $total = 0;
        foreach ($availableSocialNetworks as $socialNetwork)
        {
            $methodName = sprintf('getCount%s', $socialNetwork);
            if (method_exists($this, $methodName))
            $total += call_user_func(array($this, $methodName), $url);
        }
        return $total;
    }
}
