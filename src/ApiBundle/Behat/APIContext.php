<?php

namespace ApiBundle\Behat;

use AppBundle\Behat\BaseContext;
use ApiBundle\Security\Firewall\JWTListener;
use Behat\Gherkin\Node\PyStringNode;
use Behat\Behat\Hook\Scope\AfterStepScope;
use Behat\Testwork\Tester\Result\TestResult;
use Symfony\Component\HttpFoundation\StreamedResponse;

class APIContext extends BaseContext
{
    private $client;

    /**
     * @BeforeScenario
     */
    public function refreshState()
    {
        $this->client = $this->get('test.client'); // since it is prototype, it will always be new
        $this->client->setServerParameters([
            // 'HTTP_HOST' => 'api.myhost.com', // if it is not localhost
            'HTTP_ACCEPT' => 'application/json', // only accept json
            'CONTENT_TYPE' => 'application/json', // only POST, PUT json
            'HTTP_LANGUAGE' => 'en', // only english
        ]);
    }

    /**
     * @AfterStep
     */
    public function showLastResponse(AfterStepScope $scope)
    {
        if ($scope->getTestResult()->getResultCode() == TestResult::FAILED) {
            if ($response = $this->client->getResponse()) {
                if ($response instanceof StreamedResponse) {
                    echo "\nLast response status: {$response->getStatusCode()}, content is a STREAM, cannot dump it..\n\n";
                } else {
                    $body = $response->getContent();
                    $json = json_decode($body, true);
                    if ($json !== null) {
                        $body = json_encode($json, JSON_PRETTY_PRINT);
                    }
                    echo "\nLast response status: {$response->getStatusCode()}, content:\n\n";
                    echo "\n{$body}\n\n";
                }
            }
        }
    }

    /**
     * @When /^I try to authenticate as "([^"]+)"$/
     * @When /^I try to authenticate as "([^"]+)" with password "([^"]+)"$/
     */
    function iTryToAuthenticatedAs($username, $password = null)
    {
        $response = $this->request('POST', '/api/authenticate', [
            'username' => $username,
            'password' => $password ?: 'S3cretpassword',
        ]);

        // store authentication token if successful
        if ($response->getStatusCode() === 200) {
            $token = json_decode($response->getContent(), true)['token'];
            $authorizationHeader = implode(' ', [JWTListener::HEADER_PREFIX, $token]);
            $this->client->setServerParameter('HTTP_AUTHORIZATION', $authorizationHeader);
        }
    }

    /**
     * @Given /^I'm authenticated as "([^"]+)"$/
     */
    function imAuthenticatedAs($username)
    {
        $this->iTryToAuthenticatedAs($username);

        if ($this->client->getResponse()->getStatusCode() !== 200) {
            throw new \Exception("Authentication has failed. Check if there is an user: {$username}");
        }
    }

    private function request($method, $path, array $params = [], array $headers = [])
    {
        $path = $this->placeholders->replace($path);
        $content = null;
        $parameters = [];
        switch ($method) {
        case 'GET':
        case 'DELETE':
            $parameters = $params;
            break;
        default:
            $content = json_encode($params);
            break;
        }

        $files = [];
        $this->client->request($method, $path, $parameters, $files, $headers, $content);
        return $this->client->getResponse();
    }

    /**
     * @When /^(?:I )?send (GET|DELETE|PUT) request to "([^"]+)"$/
     */
    function iSendRequest($method, $uri)
    {
        $this->request($method, $uri);
    }

    /**
     * @When /^(?:I )?send (POST|PUT) request to "([^"]+)" with:$/
     */
    function iSendRequestWithData($method, $uri, PyStringNode $body)
    {
        $body = $body->getRaw();
        $body = $this->placeholders->replace($body);
        $json = json_decode($body, true);

        if (null === $json) {
            throw new \RuntimeException("Cannot convert request body to json:\n{$body}\nlast json err: ".json_last_error_msg());
        }
        $this->request($method, $uri, $json);
    }

    /**
     * @Given /^(?:the )?response code is (\d+)$/
     * @Then /^(?:the )?response code should be (\d+)$/
     */
    function theResponseStatusShouldBe($code)
    {
        if (null === $this->client->getResponse()) {
            throw new \Exception("There is no response generated, make an api request first");
        }

        if (intval($code) !== $this->client->getResponse()->getStatusCode()) {
            throw new \Exception("Expected status {$code} does not match in last response to: {$this->client->getResponse()->getStatusCode()}");
        }
    }

    /**
     * @Then /^there should be an error message "([^"]+)" in response$/
     */
    function thereShouldBeAnErrorMessageInResponse($msg)
    {
        $msg = $this->placeholders->replace($msg);
        if (null === $this->client->getResponse()) {
            throw new \Exception("There is no response generated, make an api request first");
        }

        $json = json_decode($str = $this->client->getResponse()->getContent(), true);

        if (null === $json) {
            throw new \RuntimeException("Cannot convert response to json:\n{$str}\nlast json err: ".json_last_error_msg());
        }

        if (!array_key_exists('error', $json)) {
            throw new \Exception("Response does not have any errors");
        }

        if (!preg_match('/'.$msg.'/', $json['error']['message'])) {
            throw new \Exception("Response error: '{$json['error']['message']}' does not contain '{$msg}'");
        }
    }

    /**
     * @Then /^(?:the )?response should have a header "([^"]+)" similar to "([^"]+)"$/
     */
    function theResponseShouldHaveHeader($header, $value)
    {
        if (null === $this->client->getResponse()) {
            throw new \Exception("There is no response generated, make an api request first");
        }

        $actual = $this->client->getResponse()->headers->get($header);

        if (!preg_match('#'.$value.'#smi', $actual)) {
            throw new \Exception("Response header: {$header} value {$actual}, does not match expected value - {$value}");
        }
    }

    /**
     * @Then /^(?:the )?response should contain "([^"]*)"$/
     */
    function theResponseShouldContain($text)
    {
        $text = $this->placeholders->replace($text);
        if (null === $this->client->getResponse()) {
            throw new \Exception("There is no response generated, make an api request first");
        }

        if (!preg_match("/{$text}/", $this->client->getResponse()->getContent())) {
            throw new \Exception("The response does not contain: {$text}");
        }
    }

    /**
     * @Then /^(?:the )?response should not contain "([^"]*)"$/
     */
    function theResponseShouldNotContain($text)
    {
        $text = $this->placeholders->replace($text);
        if (null === $this->client->getResponse()) {
            throw new \Exception("There is no response generated, make an api request first");
        }

        if (preg_match("/{$text}/", $this->client->getResponse()->getContent())) {
            throw new \Exception("The response was not expected to contain: {$text}");
        }
    }

    /**
     * @Then /^(?:the )?response should contain json:$/
     */
    function theResponseShouldContainJson(PyStringNode $jsonString)
    {
        if (null === $this->client->getResponse()) {
            throw new \Exception("There is no response generated, make an api request first");
        }

        $json = $this->placeholders->replace($jsonString->getRaw());
        $etalon = json_decode($json, true);
        $actual = json_decode($this->client->getResponse()->getContent(), true);

        if (null === $etalon) {
            throw new \RuntimeException("Cannot convert etalon to json:\n{$json}\nlast json err: ".json_last_error_msg());
        }

        $assertUnorderedArrayHasEntries = function(&$a, &$b) use (&$assertUnorderedArrayHasEntries) {
            foreach ($b as $key => $val) {
                if (!array_key_exists($key, $a)) {
                    throw new \Exception("Actual array does not have '{$key}' key");
                }

                if (is_array($val)) {
                    $assertUnorderedArrayHasEntries($a[$key], $b[$key]);
                } elseif (is_numeric($key) && !in_array($val, $a, true)) {
                    throw new \Exception("Actual array does not contain value '{$val}'");
                } elseif ($a[$key] !== $val && !@preg_match($val, $a[$key])) {
                    throw new \Exception("Actual array value '{$a[$key]}' has not matched to '{$val}'");
                }
            }
        };

        $assertUnorderedArrayHasEntries($actual, $etalon);
    }

    /**
     * @Then /^(?:the )?response should match json:$/
     */
    function theResponseShouldMatchJson(PyStringNode $jsonString)
    {
        if (null === $this->client->getResponse()) {
            throw new \Exception("There is no response generated, make an api request first");
        }

        $json = $this->placeholders->replace($jsonString->getRaw());
        $etalon = json_decode($json, true);
        $actual = json_decode($this->client->getResponse()->getContent(), true);

        if (null === $etalon) {
            throw new \RuntimeException("Cannot convert etalon to json:\n{$json}\nlast json err: ".json_last_error_msg());
        }

        $assertUnorderedArraysAreEqual = function(array &$a, array &$b) use (&$assertUnorderedArraysAreEqual) {
            if (count($a) !== count($b)) {
                throw new \Exception("Actual array size [".count($b)."] does not match expected: [".count($a).']');
            }

            foreach ($b as $key => $val) {
                if (!array_key_exists($key, $a)) {
                    throw new \Exception("Actual array does not have '{$key}' key");
                }

                if (is_array($val)) {
                    if (count($a[$key]) !== count($b[$key])) {
                        throw new \Exception("Actual array size [".count($b[$key])."] for key: [{$key}] does not match expected: [".count($a[$key]).']');
                    }

                    $assertUnorderedArraysAreEqual($a[$key], $b[$key]);
                } elseif (is_bool($val) and $val !== $a[$key]) {
                    throw new \Exception("Expected boolean value of '{$key}' was not matched");
                } elseif (is_numeric($key) && !in_array($val, $a, true)) {
                    throw new \Exception("Actual array does not contain value '{$val}'");
                } elseif ($a[$key] !== $val && !@preg_match($a[$key], $val)) {
                    throw new \Exception("Actual array value '{$a[$key]}' has not matched to '{$val}'");
                }
            }
        };
        $assertUnorderedArraysAreEqual($etalon, $actual);
    }

    /**
     * @Then /^(?:the )?response should not contain json:$/
     */
    function theResponseShouldNotContainJson(PyStringNode $jsonString)
    {
        if (null === $this->client->getResponse()) {
            throw new \Exception("There is no response generated, make an api request first");
        }

        $json = $this->placeholders->replace($jsonString->getRaw());
        $etalon = json_decode($json, true);
        $actual = json_decode($this->client->getResponse()->getContent(), true);

        if (null === $etalon) {
            throw new \RuntimeException("Cannot convert etalon to json:\n{$json}\nlast json err: ".json_last_error_msg());
        }

        $assertUnorderedArrayDoesNotHaveEntries = function(&$a, &$e) use (&$assertUnorderedArrayDoesNotHaveEntries) {
            foreach ($e as $key => $val) {
                if (isset($a[$key])) {
                    // check val then
                    if (is_array($val)) {
                        $assertUnorderedArrayDoesNotHaveEntries($a[$key], $e[$key]);
                    } elseif (is_numeric($key) && in_array($val, $a, true)) {
                        throw new \Exception("Actual array contains value '{$val}'");
                    } elseif ($a[$key] === $val) {
                        throw new \Exception("Actual array value '{$a[$key]}' is same as '{$val}'");
                    }
                }
            }
        };

        $assertUnorderedArrayDoesNotHaveEntries($actual, $etalon);
    }
}
