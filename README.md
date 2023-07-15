# BardAPI-PHP
The PHP package that returns response of Google Bard through cookie value in pretty view.

I was inspired to develop after publishing a pull request at https://github.com/pj8912/php-bard-api. The main part of the code is taken from here, but further development will not depend on it.

I welcome comments or bug reports you have.

# Before using
**⚠ WARNING!!! Before you got cookies, please sign in to your Google account from a clean browser session or in incognito mode!
In the event that the __Secure-1PSIDTS cookie becomes corrupted, you may need to re-login to all accounts that you previously signed in to.**

Before using this code, you need get "keys" - cookies from Google Bard Page.
- Open [bard.google.com](https://bard.google.com/).
- Open developer tools (press F12), click on `Application` tab.
- In Application under the `Storage` you will find `cookies` dropdown.
- Under cookies click on `https://bard.google.com` which will show you all the cookies being used as `Name` and `Value`
- Copy the next cookies:
  __Secure-1PSID` and `__Secure-1PSIDTS`
- Add them to file API_KEYS.txt:

  Default API_KEYS.txt file:
  ```txt
  _BARD_API_KEY_1PSID: ****************************************
  _BARD_API_KEY_1PSIDTS: sidts-****************************************
  ```

# Using
```php
require "Bard.php";

use Pixkk\PhpBardApi\Bard;

$bard = new Bard();
$input_text = "Your question";  // Input text for the conversation
$result = $bard->get_answer($input_text);  // Get the response from Bard
var_dump($result); // Access the result data
$conversation_id = $result["conversation_id"];
$response_id = $result["response_id"];
$factualityQueries = $result["factualityQueries"];
$textQuery = $result["textQuery"];
$choices = $result["choices"];
$content = $result["content"];

print($result['choices'][0]['content'][0]);
```
![image](https://github.com/pixkk/BardAPI-PHP/assets/30828435/00333faf-7bc8-44f0-8776-fa6432f24d4d)


