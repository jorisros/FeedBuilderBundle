
# FeedBuilderBundle
Feedbuilder bundle is export bundle that helps to export data of pimcore to other systems.

## How does it works?
The working is quite simple, you discibe your export method to the level you want, that's give you data you want. This is based on the output channel bundle

## Events
Inside the builder we can subscribe to events on different levels of the building of the feeds. 

| Event                       | Description  |
| --------------------------- | ------------ |
| feedbuilder.before.run      |              |
| feedbuilder.after.selection |              |
| feedbuilder.before.row      |              |
| feedbuilder.after.row       |              |
| feedbuilder.after.run       | Latest step of building the feed, this is triggered after looping through the objects. The input of the event is de result in a array.             |

## Example of a export to JSON
Create a class and a method where the writer is been located.

```php
<?php

namespace FeedBuilderBundle\EventListener;

use FeedBuilderBundle\Event\FeedBuilderEvent;

class ExportExample
{
    const FEED_TITLE = 'Testfeed';

    public function fileHandler(FeedBuilderEvent $event){

        if($event->getConfig()->get('title') === self::FEED_TITLE)
        {
            $arr['products'] = $event->getResult();

            $dir = PIMCORE_SYSTEM_TEMP_DIRECTORY.DIRECTORY_SEPARATOR.'export';

            $name = 'json_export_'.time().'.json';
            if(!file_exists($dir)){
                mkdir($dir);
            }
            file_put_contents($dir.DIRECTORY_SEPARATOR.$name,json_encode($arr, JSON_PRETTY_PRINT));
        }
    }
}
```

Now we have a writer that writes the json content to a file. Now we have to connect the listener after that the export has been runned. 
We will do that in the ```service.yml```

```yaml
    FeedBuilderBundle\EventListener\ExportExample:
        tags:
            - { name: kernel.event_listener, event: feedbuilder.after.run, method: fileHandler }
```

