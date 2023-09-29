import { Controller } from '@hotwired/stimulus';
import * as Turbo from "@hotwired/turbo"

/*
 * Ce contrôleur permet de charger une page de détail/édition
 * sur clic d'une ligne d'une table de liste d'élèments.
 * et de filtrer les lignes de la table selon un critère de recherche.
 */
export default class extends Controller {
    
    static targets = [ "source", "filterable" ]

    filter(event) {
        let lowerCaseFilterTerm = this.sourceTarget.value.toLowerCase()
    
        this.filterableTargets.forEach((el, i) => {
          let filterableKey =  el.getAttribute("data-tableManager-key").toLowerCase()
    
          el.classList.toggle("filter--notFound", !filterableKey.includes( lowerCaseFilterTerm ) )
        })
    }

    show(event) {
        Turbo.visit(event.params.url)
    }
}
