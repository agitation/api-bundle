ag.api.Object.register({
    "common.v1/Period": {
        "props": {
            "from": {
                "type": "object",
                "class": "common.v1/Date"
            },
            "until": {
                "type": "object",
                "class": "common.v1/Date"
            }
        }
    },
    "common.v1/Location": {
        "props": {
            "lat": {
                "type": "float",
                "minValue": -90,
                "maxValue": 90
            },
            "lon": {
                "type": "float",
                "minValue": -180,
                "maxValue": 180
            }
        }
    },
    "common.v1/Month": {
        "props": {
            "month": {
                "type": "integer",
                "minValue": 1,
                "maxValue": 12
            },
            "year": {
                "type": "integer",
                "minValue": 2000,
                "maxValue": 2100
            }
        }
    },
    "common.v1/Money": {
        "props": {
            "amount": {
                "type": "float"
            },
            "currency": {
                "type": "string",
                "minLength": 3,
                "maxLength": 3
            }
        }
    },
    "common.v1/Response": {
        "props": {
            "payload": {
                "type": "raw",
                "nullable": true
            },
            "entityList": {
                "type": "raw",
                "nullable": true,
                "default": []
            }
        }
    },
    "common.v1/DateTime": {
        "props": {
            "day": {
                "type": "integer",
                "minValue": 1,
                "maxValue": 31
            },
            "month": {
                "type": "integer",
                "minValue": 1,
                "maxValue": 12
            },
            "year": {
                "type": "integer",
                "minValue": 2000,
                "maxValue": 2100
            },
            "hour": {
                "type": "integer",
                "minValue": 0,
                "maxValue": 23
            },
            "minute": {
                "type": "integer",
                "minValue": 0,
                "maxValue": 59
            }
        }
    },
    "common.v1/Date": {
        "props": {
            "day": {
                "type": "integer",
                "minValue": 1,
                "maxValue": 31
            },
            "month": {
                "type": "integer",
                "minValue": 1,
                "maxValue": 12
            },
            "year": {
                "type": "integer",
                "minValue": 2000,
                "maxValue": 2100
            }
        }
    },
    "common.v1/Time": {
        "props": {
            "hour": {
                "type": "integer",
                "minValue": 0,
                "maxValue": 23
            },
            "minute": {
                "type": "integer",
                "minValue": 0,
                "maxValue": 59
            }
        }
    }
});
