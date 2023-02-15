<?php
/**
 * The plugin bootstrap file
 *
 * @link              http://rentalsunited.com/
 * @since             0.0.1
 * @package           RUB
 *
 * @wordpress-plugin
 */

add_action( 'plugins_loaded', 'RUB_user_install' );
add_filter( 'rentals_united_wp_users', 'RUB_get_users');

// main credentials
$username = get_option('username_plugin');
$password = get_option('password_plugin');

// user credentials
$username_user_credentials = get_option('username_user_credentials', '');
$password_user_credentials = get_option('password_user_credentials', '');

?>
<div class='wrap'>
    <h1 class='wp-heading-inline'>
        Rentals United
    </h1>

    <form method='post' id='RentalsUnitedForm' action=''>
        <h3>User credentials</h3>
        <label> User name:
            <input name="username" type="text" placeholder="Platform User Name" value="<?php echo $username; ?>">
        </label>
        <label> Password:
            <input name="userpassword" type="password" placeholder="Platform User Password" value="<?php echo $password; ?>">
        </label>
<!--        <h3>Main credentials</h3>-->
<!--        <label> User name:-->
<!--            <input name="username_user_credentials" type="text" placeholder="Platform User Name" value="--><?php //echo $username_user_credentials; ?><!--">-->
<!--        </label>-->
<!--        <label> Password:-->
<!--            <input name="password_user_credentials" type="password" placeholder="Platform User Password" value="--><?php //echo $password_user_credentials; ?><!--">-->
<!--        </label>-->
        <br />
        <label>
            <input type='hidden' name="action" value="wp_ajax_getPropertiesList">
            <input name='submit' type='submit' >
        </label>
    </form>

    <div class="form_return_success" id="form_return_success" style="display:none;">
        <h2 style="color:green;">Import completed successfully <a href="/wp-admin/edit.php?post_type=properties">(VIEW)</a></h2>
    </div>
    <div class="form_return_error" id="form_return_error" style="display:none;">
        <h2 style="color:red;">Wrong login or password</h2>
    </div>
    <div id="RentalsUnitedLoader" class="loader" style="position: fixed; width: 100%; height: 100%; top: 0; left: 0; background: #f1f1f1; text-align: center; display: none; padding-top: 10%;">
        <div><img src="<?php echo plugin_dir_url( __DIR__ ); ?>img/spin.svg" alt=""></div>
        <div style="text-align: center;">
            Synchronizing
            <span id="progress_loading_current">0</span> property out of total <span id="progress_loading_total">0</span>
        </div>
        <div><progress id="progress_loading" max="100" value="0" style="height: 32px; width: 400px;"></progress></div>
    </div>


    <script>
        const properties = [];
        document.getElementById('RentalsUnitedForm').addEventListener('submit', function (event) {
            event.preventDefault();
            postData({
                action: "getPropertiesList",
                username: document.querySelector('#RentalsUnitedForm [name="username"]').value,
                userpassword: document.querySelector('#RentalsUnitedForm [name="userpassword"]').value,
                // username_user_credentials: document.querySelector('#RentalsUnitedForm [name="username_user_credentials"]').value,
                // password_user_credentials: document.querySelector('#RentalsUnitedForm [name="password_user_credentials"]').value
            })
                .then((response) => {
                    if (response.hasOwnProperty('message')) {
                        document.getElementById('form_return_error').style.display = 'block';
                    }
                    else {
                        document.getElementById('form_return_error').style.display = 'none';
                        properties.push(...response);
                        document.getElementById('progress_loading').setAttribute('max', properties.length.toString());
                        document.getElementById('progress_loading').setAttribute('value', '0');
                        document.getElementById('progress_loading_current').innerText = '0';
                        document.getElementById('progress_loading_total').innerText = properties.length.toString();
                        document.getElementById('RentalsUnitedLoader').style.display = 'block';
                        document.getElementById('form_return_success').style.display = 'none';
                        loadProperties();
                    }
                })
                .catch((reason => console.log(reason)));
        });

        /**
         * Json to form url encoded
         *
         * @return {string}
         */
        function JSON_to_URLEncoded(element,key,list){
            list = list || [];
            if(typeof(element)=='object'){
                for (let idx in element) {
                    if(element.hasOwnProperty(idx)) {
                        JSON_to_URLEncoded(element[idx], key ? key + '[' + idx + ']' : idx, list);
                    }
                }
            } else {
                list.push(key+'='+encodeURIComponent(element));
            }
            return list.join('&');
        }

        /**
         * Post request
         *
         * @param data
         * @returns {Promise<any>}
         */
        async function postData(data = {}) {
            const response = await fetch(ajaxurl, {
                method: 'POST', // *GET, POST, PUT, DELETE, etc.
                mode: 'cors', // no-cors, *cors, same-origin
                cache: 'no-cache', // *default, no-cache, reload, force-cache, only-if-cached
                credentials: 'same-origin', // include, *same-origin, omit
                headers: {
                    //'Content-Type': 'application/json'
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                redirect: 'follow', // manual, *follow, error
                referrerPolicy: 'no-referrer', // no-referrer, *client
                body: JSON_to_URLEncoded(data) // body data type must match "Content-Type" header
            });
            return await response.json(); // parses JSON response into native JavaScript objects
        }

        async function loadProperties() {
            let loaded = 0;
            let error = 0;
            let current, data;
            let authorized = true;
            while (properties.length) {
                current = properties.shift();
                data = {
                    action: "getPropertiesBooking",
                    ID: current.ID,
                    PUID: current.PUID,
                    OwnerID: current.OwnerID,
                    DetailedLocationID: current.DetailedLocationID
                };
                await postData(data)
                    .then(response => {
                        if (response.hasOwnProperty('authorized')) {
                            authorized = false;
                        }
                        console.log(response);
                        loaded++;
                        return loaded;
                    })
                    .catch(reason => {
                        error++;
                        return error;
                    });
                if (!authorized) {
                    break;
                }
                document.getElementById('progress_loading_current').innerText = loaded.toString();
                document.getElementById('progress_loading').setAttribute('value', loaded.toString());
            }
            document.getElementById('RentalsUnitedLoader').style.display = 'none';
            if (authorized) {
                document.getElementById('form_return_success').style.display = 'block';
            }
            else {
                document.getElementById('form_return_error').style.display = 'block';
            }
        }
    </script>
</div>
<?php

