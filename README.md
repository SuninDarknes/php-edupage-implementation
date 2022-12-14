# php-edupage-implementation
Part of edupage-api rewritten in php

Program logins in edupage user website and uses subtitute function to read data from site.

Only changes in file that are needed is to:
```php
2 $subdomain = "";
3 $username ="";
4 $password = "";
```
<br>
<br>
<hr>


functions from "edupage-api" rewritten:

login.py
```python
def login(self, username: str, password: str, subdomain: str):
        """Login while specifying the subdomain to log into.
        Args:
            username (str): Your username.
            password (str): Your password.
            subdomain (str): Subdomain of your school (https://{subdomain}.edupage.org).
        Raises:
            BadCredentialsException: Your credentials are invalid.
        """

        request_url = f"https://{subdomain}.edupage.org/login/index.php"

        csrf_response = self.edupage.session.get(request_url).content.decode()

        csrf_token = csrf_response.split("name=\"csrfauth\" value=\"")[1].split("\"")[0]

        parameters = {
            "username": username,
            "password": password,
            "csrfauth": csrf_token
        }

        request_url = f"https://{subdomain}.edupage.org/login/edubarLogin.php"

        response = self.edupage.session.post(request_url, parameters)

        if "bad=1" in response.url:
            raise BadCredentialsException()

        self.__parse_login_data(response.content.decode())
        self.edupage.subdomain = subdomain
```
substitution.py
```python
class Substitution(Module):
    def __get_substitution_data(self, date: date) -> str:
        url = (f"https://{self.edupage.subdomain}.edupage.org/substitution/server/viewer.js"
               "?__func=getSubstViewerDayDataHtml")

        data = {
            "__args": [
                None,
                {
                    "date": date.strftime("%Y-%m-%d"),
                    "mode": "classes"
                }
            ],
            "__gsh": self.edupage.gsec_hash
        }

        response = self.edupage.session.post(url, json=data).content.decode()
        response = json.loads(response)

        if response.get("reload"):
            raise ExpiredSessionException("Invalid gsec hash! "
                                          "(Expired session, try logging in again!)")

        return response.get("r")
```
