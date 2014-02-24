ImageBundle
===========

####TODO: 

- Docs


### USE:
POST to /upload
        
        files[] : uploaded file, 
        {
          "operations": [
            {
              "resize": {
                "width": 20,
                "height": 20
              },
              "rotate": {
                "degrees": 90
              },
              "crop": {
                "x": 0,
                "y": 0,
                "w": 10,
                "h": 10
              },
              "convert": true
            },
            {
              "resize": {
                "width": 20,
                "height": 20
              }
            }
          ],
          "meta": {
            "name": "pun.jpg",
            "tags": [
              "foo",
              "bar",
              "baz"
            ],
            "user": {
              "id": 1,
              "company": 1
            }
          }
        }