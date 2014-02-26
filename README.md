ImageBundle
===========

####TODO:
[![Scrutinizer Quality Score](https://scrutinizer-ci.com/g/uecode/image-bundle/badges/quality-score.png?s=4d2441bfa4e1e1c59c952fe671c1ffd372fe2925)](https://scrutinizer-ci.com/g/uecode/image-bundle/)

[![Code Coverage](https://scrutinizer-ci.com/g/uecode/image-bundle/badges/coverage.png?s=6b30a179fbf7c13d78a6b458dc8ced6695ef468f)](https://scrutinizer-ci.com/g/uecode/image-bundle/)

`app/AppKernel.php`

    $bundles = [
        ...
        new Uecode\Bundle\ImageBundle\UecodeImageBundle(),
        ...
    ];

`app/config/config.yml`

    # Sample Config
    uecode_image:
        route: upload # Defaults to upload
        use_queue: false # true || false - Will utilize qpush bundle
        upload_dir: false # < DIR Name > || false - Local Upload Dir /Symfony/web/bundles/uecode_image/upload
        tmp_dir: tmp # Local TMP Dir - /Symfony/web/bundles/uecode_image/tmp
        gregwar:
            cache_dir: cache
            throw_exception: true
            fallback_image: %kernel.root_dir%/../web/bundles/uecode_image/tmp/2ed2eeb6c3318342ace12cc60f661258.jpeg
            web_dir: %kernel.root_dir%/../web
        aws:
            s3: # false || values below
                key: < YOUR AWS KEY >
                secret: < YOUR AWS SECRET >
                region: < YOUR AWS REGION >
                bucket: < YOUR AWS BUCKET >
                directory: < YOUR AWS DIRECTORY ( Optional ) >


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
              }
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