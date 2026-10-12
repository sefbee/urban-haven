import requests
import sys

USERNAME = "urbavntd"
PASSWORD = "nCmtCCO90JRu"
CPANEL_URL = "https://server71.web-hosting.com:2083"

session = requests.Session()
session.verify = False  # Ignore SSL cert errors just in case

login_url = f"{CPANEL_URL}/login/?login_only=1"
data = {"user": USERNAME, "pass": PASSWORD}
print("Attempting login...")
try:
    resp = session.post(login_url, data=data, timeout=30)
    print(f"Status Code: {resp.status_code}")
    print(f"Response Body: {resp.text}")
except Exception as e:
    print(f"Error: {e}")

