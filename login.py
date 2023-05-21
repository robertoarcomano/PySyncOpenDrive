"""
Login to OpenDrive API Server
"""
# 1 Web Connecion to https://dev.opendrive.com/api/v1/session/login.json: OK
# 2 Use Auth and Password to access: OK
# 3 Retrieve a new SessionID: OK
# 4 Try using the new SessionID: STARTED
import requests
import json
from subprocess import run


class OpenDriveAPI:
    GPG_FILE=["/usr/bin/gpg", "-d", "/home/berto/secrets.gpg"]
    LOGIN_URL="https://dev.opendrive.com/api/v1/session/login.json"
    DOWNLOAD_URL="https://dev.opendrive.com/api/v1/download/file.json/"
    auth_data = None
    session = None
    @classmethod
    def set_auth(cls):
        if cls.auth_data is not None:
            return
        process = run(cls.GPG_FILE, capture_output=True, text=True)
        passwd=process.stdout[:-1]
        cls.auth_data = {
            'username': 'bertolinux@gmail.com',
            'passwd': passwd,
            'version': '10',
            'partner_id': '',
        }
        cls.upload = {
            "file_id": ""
        }
    @classmethod
    def __login(cls):
        cls.set_auth()
        cls.session = requests.post(cls.LOGIN_URL, json=cls.auth_data)
    @classmethod
    def get_session(cls):
        return json.loads(cls.session.text)
    def __init__(self):
        OpenDriveAPI.__login()
    # def download(self, file_path):
    #     ret = requests.get(OpenDriveAPI.DOWNLOAD_URL+file_path+"&session_id="+OpenDriveAPI.get_session()["SessionID"])
    #     print(ret)
    # def upload(self, file_path, file):
    #     ret = requests.post(OpenDriveAPI.DOWNLOAD_URL+file_path+"&session_id="+OpenDriveAPI.get_session()["SessionID"])
    #     print(ret)

oda = OpenDriveAPI()
print(oda.get_session())
# print(oda.download("/test_file.txt"))

