/**
 * GPS Plugin
 *
 * This plugin gets the geolocation of
 * the user based on the navigator settings
 * and fallback IP based API.
 */
export function geoLocate(options = {}) {

    //Init the plugin in the global scope
    window.GPS = geolocation.init(options)
}


export const geolocation = {
    defaults : {
        //If we want to ask for permission on init
        prompt : true,
        //The api.ipdata.co api key for fallback
        apikey: "f4ca6ff7e5152e9208fef21603d61a47140ec4dc8e11018b333d4bc1"
    },
    settings: {},
    //Navigator Service Status
    serviceIsActive:false,
    //Permission Service Status
    permissionsIsActive:false,
    //Location Permission State
    permissionsStatus:'inactive',
    //Plugin execution status
    isFinished:false,
    //Default Found position lat,lng
    position:{lat: 0,lng: 0},
    //Bounds for lat,lng if any
    //To disable bounds enter null
    // bounds = null,
    bounds:{
        north: 17.70,
        south: 17.05,
        west: 78.05,
        east: 78.90
    },
    // bounds = {north: 22.70,south: 17.05,west: 22.05,east: 78.90},
    //Default position for bounds checking
    isInBounds:false,
    //Default position for bounds checking
    positionDefault:{
        lat: 17.40010939119478,
        lng: 78.48258630887469
    },
    //IP or Navigator fetch type
    isAccurate:false,
    //If invoke permission function is triggered
    invoke:false,
    checkEnv:function() {

        if (navigator.geolocation) {

            this.serviceIsActive = true;
        }

        if (navigator.permissions) {

            this.permissionsIsActive = true;
        }
    },
    start:function() {

        //Check the environment variables
        this.checkEnv();

        //Check if navigator is active
        if (!this.serviceIsActive){

            this.handleGeoError();
        }

        //Check if permissions is active
        if (this.permissionsIsActive){

            this.makePermissionsQuery();
        }else{

            this.makePositionQuery();
        }
    },
    handleGeoError:function() {

        fetch('https://api.ipdata.co/?api-key='+this.settings.apikey)
            .then(res => res.json())
            .then(data => {
                console.log(data)
                if (settings.env === 'dev'){

                    console.log('aptoGeoLocation: DEBUG - handleGeoError');
                    console.log(data);
                }

                this.isAccurate = false;
                this.position = {
                    lat: parseFloat(data.latitude),
                    lng: parseFloat(data.longitude)
                }

                this.checkPositionBounds();

                this.isFinished = true;
            })
        // this.isAccurate = false;
        // this.position = {
        //     lat: 17.40010939119478,
        //     lng: 78.48258630887469
        // }
        //
        // this.checkPositionBounds();
        //
        // this.isFinished = true;
    },
    checkPositionBounds:function () {

        //Check if bounds are set and if position is in bounds
        if (this.position.lat < this.bounds.south || this.position.lat > this.bounds.north || this.position.lng > this.bounds.east || this.position.lng < this.bounds.west){

            this.position = this.positionDefault;
        }else{

            this.isInBounds = true;
        }
    },
    makePermissionsQuery:function() {

        let _this = this
        navigator.permissions.query({name: 'geolocation'}).then(function (result) {

            _this.permissionsStatus = result.state;

            switch (result.state) {

                case "granted":

                    _this.makePositionQuery();
                    break;
                case "prompt":

                    //If prompt on init is active ask for permission directly to pop allow location window otherwise wait for invoke
                    if (_this.settings.prompt === true){

                        _this.makePositionQuery();
                    }else{

                        _this.handleGeoError();
                    }
                    break;
                case "denied":

                    _this.handleGeoError();
                    break;
            }
        });
    },
    makePositionQuery:function () {

        let _this = this
        navigator.geolocation.getCurrentPosition(function (devicePos) {

            _this.isAccurate = true;
            _this.position = {
                lat: parseFloat(devicePos?.coords.latitude),
                lng: parseFloat(devicePos?.coords.longitude)
            }

            _this.checkPositionBounds();

            //inject granted status as this is an if dependent variable
            //,so we make sure it's in place for the prompt statement
            _this.permissionsStatus = 'granted';

            if (_this.invoke === true){

                let url = new URL(window.location.href);
                let params = url.searchParams;

                params.set('invoke','1');
                params.set('p',this.position.lat+','+this.position.lng);

                url.search = params.toString();

                url = window.location.href;
                if (url.indexOf('?') !== -1){
                    url = url+'&invoke=1';
                }else{
                    url = url+'?invoke=1';
                }
                window.location = decodeURIComponent(url.toString());

            }

            _this.isFinished = true;
        },function () {

            _this.handleGeoError();
        });
    },
    init:function(options = {}) {

        this.settings = {...this.defaults,...options}

        //start the plugin
        this.start();

        //if for any reason the plugin is not finished, return the default position
        if (this.position.lat === 0 && this.position.lng === 0){
            this.position = this.positionDefault;
        }

        return this;
    }
}
