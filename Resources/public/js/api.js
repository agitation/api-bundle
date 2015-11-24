/*jslint white: true */
/*global Agit */

Agit.Endpoint.registerList([]);
Agit.Object.registerList({
    "common.v1/ObjectList": {
        "itemList": {
            "name": "itemList",
            "default": []
        }
    },
    "common.v1/String": {
        "value": {
            "name": "value",
            "default": null
        }
    },
    "common.v1/Response": {
        "success": {
            "name": "success",
            "default": null
        },
        "messageList": {
            "name": "messageList",
            "default": []
        },
        "payload": {
            "name": "payload",
            "default": null
        },
        "entityList": {
            "name": "entityList",
            "default": []
        }
    },
    "common.v1/Message": {
        "type": {
            "name": "type",
            "default": null
        },
        "code": {
            "name": "code",
            "default": null
        },
        "text": {
            "name": "Message Text",
            "default": null
        }
    }
});