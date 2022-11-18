import { Controller } from '@hotwired/stimulus';
import { frog } from "../js/frog";
import { viewportFix } from "../js/viewport";
import { geoLocate } from "../js/geolocation";
import { mobileDetect } from "../js/mobileDetect";

export default class extends Controller {
    connect() {

        //frog
        frog()

        //viewport fix for mobile devices
        viewportFix()

        //geolocation in global scope so it can be used in other controllers
        //in many cases we dont need to know the location of the user
        //so we can comment this out
        geoLocate()

        //mobile detect
        mobileDetect()
    }
}