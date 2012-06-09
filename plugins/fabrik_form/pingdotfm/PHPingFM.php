<?php

/**
 * Copyright (c) 2008 Dmitri Gaskin, http://dmitrizone.com/
 *
 * Dual licensed under the MIT
 * and GPL (http://www.gnu.org/licenses/gpl.html) licenses.
 *
 * ----- MIT LICENSE -----
Copyright (c) 2008 Dmitri Gaskin, http://dmitrizone.com/

Permission is hereby granted, free of charge, to any person obtaining
a copy of this software and associated documentation files (the
"Software"), to deal in the Software without restriction, including
without limitation the rights to use, copy, modify, merge, publish,
distribute, sublicense, and/or sell copies of the Software, and to
permit persons to whom the Software is furnished to do so, subject to
the following conditions:

The above copyright notice and this permission notice shall be
included in all copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND,
EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF
MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND
NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS BE
LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION
OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION
WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.
 *
 */

/**
 * PHPingFM API.
 *
 * @author Dmitri Gaskin <dmitrig01@gmail.com>
 */
class PHPingFM {

  /**
   * The developer's API key.
   */
  protected $api_key;

  /**
   * Debug (don't actually run requests)?
   */
  protected $debug = FALSE;

  /**
   * User's app key.
   */
  protected $user_app_key;

  /**
   * Initializes the API key, User's APP key, and cURL handler.
   *
   * @param $api_key
   *   The developer API key. If you don't have one, it can be obtained
   *   from http://ping.fm/developers/.
   * @param $user_app_key
   *   (Optional).  The user's app key.  If not entered now, it can be
   *   entered with setUserAppKey, however it must be entered before any
   *   requests can take place.
   * @param $debug
   *   (optional) Whether to have debugging enabled
   * @see setUserAppKey
   */
  function __construct($api_key, $user_app_key = NULL, $debug = FALSE) {
    $this->api_key = $api_key;
    // Since it is optional, we need to check if it's set.
    if ($user_app_key) {
      $this->user_app_key = $user_app_key;
    }
    if ($debug) {
      $this->debug = TRUE;
    }
    // Initialize the cURL.
    $this->ch = curl_init();
    curl_setopt_array($this->ch, array(
      CURLOPT_CONNECTTIMEOUT => 2,
      CURLOPT_RETURNTRANSFER => TRUE,
      CURLOPT_POST => TRUE,
      CURLOPT_USERAGENT => 'PHPingFM 0.1',
  ));
  }

  /**
   * Sets the User's APP key, if not already set.  It can also be used to change the key.
   */
  function setUserAppKey($user_app_key) {
    $this->user_app_key = $user_app_key;
  }

  /**
   * Close the cURL connection.
   */
  function __destruct() {
    curl_close($this->ch);
  }

  /**
   * Call a method on the ping.fm server.
   *
   * @param $service
   *   The end of the URL to fetch.  For example user.services will
   *   become http://api.ping.fm/v1/user.services when requesting from the server.
   * @param $fields
   *   The fields to pass in over POST.  The user's app key and the API key are
   *   automatically added.
   * @return
   *   An array with two elements ‚Äì status, which is a boolean which indicates
   *   whether the operation succeeded or not, and response, which is the XML of the
   *   response, in SimpleXML format.
   */
  protected function callMethod($service, $fields = array()) {
    // Make sure we have an app key.
    if (!isset($this->user_app_key)) {
      return array('status' => FALSE);
    }
    // Setup the cURL options.
    curl_setopt_array($this->ch, array(
      CURLOPT_POSTFIELDS => $fields + array('user_app_key' => $this->user_app_key, 'api_key' => $this->api_key, 'debug' => (int) $this->debug),
      CURLOPT_URL => 'http://api.ping.fm/v1/'. $service,
  ));
    // Load the SimpleXML.
    $xml = simplexml_load_string(curl_exec($this->ch));
    // Check the status.
    $status = ($xml['status'] == 'OK');
    return array('status' => $status, 'response' => $xml);
  }

  /**
   * Parse a message from the XML <message> format.
   *
   * @param $message
   *   A SimpleXML-ified message.
   * @return
   *   An array of message attributes.
   */
  protected function parseMessage($message) {
    // Fetch the services.
    $services = array();
    foreach ($message->services->service as $service) {
      // We need to cast to string because SimpleXML is just like that.
      $services[(string)$service['id']] = (string)$service['name'];
    }
    // Get the message id.
    $id = (string)$message['id'];
    // Assemble the message.
    $message = array(
      'method' => (string)$message['method'],
      'date' => array('rfc' => (string)$message->date['rfc'], 'unix' => (string)$message->date['unix']),
      'services' => $services,
      // All messages are Base64 encoded.  Decode them.
      'body' => base64_decode((string)$message->content->body),
  );
    // If we have a title, add it to the array.
    if (isset($message->content->title)) {
      $message['title'] = base64_decode((string)$message->content->title);
    }
    return $message;
  }

  //----------------------------------------------
  // Public API functions.

  /**
   * Public API function: user.validate: validates the
   * given user‚Äôs app key.
   *
   * @return
   *   A boolean of whether the app key is correct.
   */
  function validate() {
    $validates = $this->callMethod('user.validate');
    return $validates['status'];
  }

  /**
   * Public API function: user.services: gets a list of services
   * the particular user has set up through Ping.fm.
   *
   * @return
   *   An array of services.
   */
  function services() {
    $services = $this->callMethod('user.services');
    // If it didn't succeed, don't proceed.
    if (!$services['status']) {
      return FALSE;
    }
    $service_array = array();
    // Iterate through all services.
    foreach ($services['response']->services->service as $service) {
      $service_array[(string)$service['id']] = array(
        'name' => (string)$service['name'],
        'methods' => explode(',', (string)$service->methods),
    );
    }
    return $service_array;
  }

  /**
   * Public API function: user.triggers: gets a user‚Äôs custom triggers.
   *
   * @return
   *   An array of a user's custom triggers.
   */
  function triggers() {
    $triggers = $this->callMethod('user.triggers');
    // If it didn't succeed, don't proceed.
    if (!$triggers['status']) {
      return FALSE;
    }
    $trigger_array = array();
    // Iterate through triggers.
    foreach ($triggers['response']->triggers->trigger as $trigger) {
      $services = array();
      // Iterate through all of the trigger's serivces.
      foreach ($trigger->services->service as $service) {
        $services[(string)$service['id']] = (string)$service['name'];
      }
      $trigger_array[(string)$trigger['id']] = array(
        'method' => (string)$trigger['method'],
        'services' => $services,
    );
    }
    return $trigger_array;
  }

  /**
   * Public API function: user.latest: gets the last 25 messages a
   * user has posted through Ping.fm.
   *
   * @param $limit
   *   Limit the results returned.  Default is 25.
   * @param $order
   *   Which direction to order the returned results by
   *   date.  Default is DESC (Descending).
   * @return
   *   An array of a user's latest messages.
   */
  function latest($limit = 25, $order = "DESC") {
    $messages = $this->callMethod('user.latest', array('limit' => $limit, 'order' => $order));
    // If it didn't succeed, don't proceed.
    if (!$messages['status']) {
      return FALSE;
    }
    $messages_array = array();
    // Go through each message, parsing them
    foreach ($messages['response']->messages->message as $message) {
      $messages_array[(string)$message['id']] = $this->parseMessage($message);
    }
    return $messages_array;
  }

  /**
   * Public API function: user.post: posts a message to the user‚Äôs Ping.fm services.
   *
   * @param $post_method
   *   Posting method.  Either "blog", "microblog" or "status."
   * @param $body
   *   Message body.
   * @param $title
   *   Title of the posted message.  This will only appear if the specified
   *   service supports a title field.  Otherwise, it will be discarded.
   * @param $services
   *   A single service or array of services to post to.
   *   Default is all services set up for specified method.  If the posted
   *   method is not supported by service, the request will return an error.
   */
  function post($post_method, $body, $title = NULL, $services = NULL) {
    $fields = array('post_method' => $post_method, 'body' => $body);
    if ($title) {
      $fields['title'] = $title;
    }
    if ($services) {
      $fields['service'] = implode(',', $services);
    }
    $response = $this->callMethod('user.post', $fields);
    return $response['status'];
  }

  /**
   * Public API function: user.tpost: posts a message to the user‚Äôs Ping.fm
   * services using one of their custom triggers.
   *
   * @param $trigger
   *   Custom trigger the user has defined from the Ping.fm website.
   * @param $body
   *   Message body.
   * @param $title
   *   Title of the posted message.  This will only appear if the specified
   *   service supports a title field.  Otherwise, it will be discarded.
   */
  function tpost($trigger, $body, $title = NULL) {
    $fields = array('trigger' => $trigger, 'body' => $body);
    if ($title) {
      $fields['title'] = $title;
    }
    $response = $this->callMethod('user.tpost', $fields);
    return $response['status'];
  }
}
