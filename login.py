"""
Login to OpenDrive API Server
"""
# 1 Web Connecion to https://dev.opendrive.com/api/v1/session/login.json: OK
# 2 Use Auth and Password to access: OK
# 3 Retrieve a new SessionID: OK
# 4 Try using the new SessionID
import requests
import json
from subprocess import run


class OpenDriveAPI:
    GPG_FILE=["/usr/bin/gpg", "-d", "/home/berto/secrets.gpg"]
    LOGIN_URL="https://dev.opendrive.com/api/v1/session/login.json"
    AUTH_DATA=""
    @classmethod
    def set_auth(cls):
        process = run(cls.GPG_FILE, capture_output=True, text=True)
        passwd=process.stdout[:-1]
        cls.AUTH_DATA = {
            'username': 'bertolinux@gmail.com',
            'passwd': passwd,
            'version': '10',
            'partner_id': '',
        }
    def __login(self):
        self.connection = requests.post(OpenDriveAPI.LOGIN_URL, json=OpenDriveAPI.AUTH_DATA)
    def get_connection(self):
        return json.loads(self.connection.text)
    def __init__(self):
        OpenDriveAPI.set_auth()
        self.__login()


oda = OpenDriveAPI()
print(oda.get_connection())

