import { Controller } from '@hotwired/stimulus';
import * as Turbo from "@hotwired/turbo"

/*
 * Ce contrôleur permet de charger une rencontre à parir de la page
 * de la liste des rencontres.
 */
export default class extends Controller {

    show(event) {
        Turbo.visit(event.params.url)
    }
}
