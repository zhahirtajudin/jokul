import hashlib
import hmac
import base64
from datetime import datetime, timezone
import requests

# Generate Digest
def generateDigest(jsonBody):  
    return base64.b64encode(hashlib.sha256(jsonBody.encode('utf-8')).digest()).decode("utf-8")

def generateSignature(clientId, requestId, requestTimestamp, requestTarget, digest, secret):
    # Prepare Signature Component
    print("----- Signature Component -----")
    componentSignature = "Client-Id:" + clientId
    componentSignature += "\n"
    componentSignature += "Request-Id:" + requestId
    componentSignature += "\n"
    componentSignature += "Request-Timestamp:" + requestTimestamp
    componentSignature += "\n"
    componentSignature += "Request-Target:" + requestTarget
    # If body not send when access API with HTTP method GET/DELETE
    if digest:
        componentSignature += "\n"
        componentSignature += "Digest:" + digest
     
    print(componentSignature)
    message = bytes(componentSignature, 'utf-8')
    secret = bytes(secret, 'utf-8')
 
    # Calculate HMAC-SHA256 base64 from all the components above
    signature = base64.b64encode(hmac.new(secret, message, digestmod=hashlib.sha256).digest()).decode("utf-8")

    # Prepend encoded result with algorithm info HMACSHA256=
    return "HMACSHA256="+signature 

# Sample of usage

# Generate Digest from JSON Body, For HTTP Method GET/DELETE don't need generate Digest
print("----- Digest -----")
jsonBody = '{\"order\":{\"invoice_number\":\"INV-20210124-0001\",\"amount\":150000},\"virtual_account_info\":{\"expired_time\":60,\"reusable_status\":false,\"info1\":\"Merchant Demo Store\"},\"customer\":{\"name\":\"Taufik Ismail\",\"email\":\"taufik@example.com\"}}'
digest = generateDigest(jsonBody)
print(digest)
print("")

client_id = "BRN-0252-1648456322620"
request_id = '7e59f4ae-2131-40a0-9957-f9cd3169a118'
request_timestamp = datetime.now(timezone.utc).replace(tzinfo=None).replace(microsecond=0).isoformat() + 'Z'
secret_key = 'SK-2zQLDKEZE8IzWWGT0BS8'

# Generate Signature
headerSignature = generateSignature(
        client_id,
        request_id,
        request_timestamp,
        "/credit-card/v1/payment-page", # For merchant request to Jokul, use Jokul path here. For HTTP Notification, use merchant path here
        digest, # Set empty string for this argumentes if HTTP Method is GET/DELETE
        secret_key)
print("----- Header Signature -----")
print(headerSignature)


# request_timestamp = datetime.now(timezone.utc).replace(tzinfo=None).replace(microsecond=0).isoformat() + 'Z'

api_url = 'https://api.doku.com/credit-card/v1/payment-page'
headers = {
    'Client-Id' : client_id,
    'Request-Id' : request_id,
    'Request-Timestamp' : request_timestamp,
    'Signature' : headerSignature,
}
print(headers)
req_cc = requests.post(api_url, headers=headers, json=jsonBody)
print(req_cc.text)