# Cleaning/deletion of items from NGINX Cache

## Introduction 

As name suggests, nginx-cache-cleaner **removes** an item or set of items from
[Nginx](http://nginx.org) cache directory.

It accepts a
[`grep` pattern]
as argument to search for cached items in the given cache directory.

The script **requires** read-write access to the cache
directory.

## Usage

**Method 1**

 1. Delete `index.html` from the `/data/nginx/cache0` cache.
 
        nginx-cache-cleaner "index.html" /data/nginx/cache0
    
 2. Delete all json files from the `/data/nginx/jsoncache` cache.
 
        nginx-cache-cleaner "\.json" /data/nginx/jsoncache 


**Method 2**

If you want to make a scheduled script/worker and make a purging engine. Then you push all the items to purged in a queue(DB/amqp etc) and purge it via purge-nginx-cache.php script.

Here, I have taken a MySQL Queue(table). Just insert the items to be purged in nginx_cache_cleaner_log table and schedule purge-nginx-cache.php script. Also, make changes in entity-cache directory confi before using it.

## Installation 

 1. Clone the repo.
    
 2. Place the script in a convenient place.

 3. Execute the scipt as specified above.
 
 4. Done.
