<?php
/**
 * Copyright 2014 Facebook, Inc.
 *
 * You are hereby granted a non-exclusive, worldwide, royalty-free license to
 * use, copy, modify, and distribute this software in source code or binary
 * form for use in connection with the web services and APIs provided by
 * Facebook.
 *
 * As with any software that integrates with the Facebook platform, your use
 * of this software is subject to the Facebook Developer Principles and
 * Policies [http://developers.facebook.com/policy/]. This copyright notice
 * shall be included in all copies or substantial portions of the software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL
 * THE AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING
 * FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER
 * DEALINGS IN THE SOFTWARE.
 *
 */

/**
 * You only need this file if you are not using composer.
 * Why are you not using composer?
 * https://getcomposer.org/
 */

if (version_compare(PHP_VERSION, '5.4.0', '<')) {
  throw new Exception('The Facebook SDK v4 requires PHP version 5.4 or higher.');
}

include('Facebook/FacebookSession.php' );
include('Facebook/FacebookSignedRequestFromInputHelper.php' );
include('Facebook/FacebookRedirectLoginHelper.php' );
include('Facebook/FacebookJavaScriptLoginHelper.php' );
include('Facebook/FacebookRequest.php' );
include('Facebook/FacebookResponse.php' );
include('Facebook/FacebookSDKException.php' );
include('Facebook/FacebookRequestException.php' );
include('Facebook/FacebookAuthorizationException.php' );
include('Facebook/GraphObject.php' );
include('Facebook/GraphUser.php' );
include('Facebook/GraphSessionInfo.php' );
include('Facebook/Entities/AccessToken.php' );
include('Facebook/Entities/SignedRequest.php' );
include('Facebook/HttpClients/FacebookCurl.php' );
include('Facebook/HttpClients/FacebookHttpable.php' );
include('Facebook/HttpClients/FacebookCurlHttpClient.php' );
include('Facebook/HttpClients/FacebookStream.php' );
include('Facebook/HttpClients/FacebookStreamHttpClient.php' );