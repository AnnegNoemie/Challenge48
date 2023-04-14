# yEAR

# Installation 
To run the project locally, you'll need :
```bash
docker fully installed
node v17+
 ```

Clone the projet and go to the corresponding folder

Then,
```bash
    docker run --rm --interactive --tty \
    --volume $PWD:/app \
    composer install --ignore-platform-reqs
```
Then,

* copy the content of .env.exemple in .env
* `npm install`
* `./vendor/bin/sail up`
* `./vendor/bin/sail artisan migrate:fresh --seed`
* `./vendor/bin/sail artisan optimize`
* `npm run dev`


# Usage


You can go on http://localhost to access the website 
You can go on http://localhost/admin to access the admin pannel and connect with romain@silvy-leligois.fr:password. The credentials are the same for the website
