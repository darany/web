import { Controller } from '@hotwired/stimulus'
import * as Turbo from '@hotwired/turbo'
import Swal from 'sweetalert2'

/*
 * Ce contrôleur permet d'afficher une modale de confirmation
 * 
 */
export default class extends Controller {

    alert(event) {
        event.preventDefault();
        Swal.fire({
            title: 'êtes-vous sûr ?',
            text: "Vous ne serez pas capable de revenir en arrière !",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Oui',
        }).then((result) => {
            if (result.isConfirmed) {
                fetch(event.params.url, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('input[id="csrf_token"]').value
                    }
                }).then(response => {
                    if (response.ok) {
                        Swal.fire(
                            'Supprimé !',
                            'Supprimé avec succès.',
                            'success'
                        )
                        this.deleteRow(event.params.rowid);
                    } else {
                        Swal.fire(
                            'Erreur !',
                            'Erreur lors de la suppression.',
                            'error'
                        )
                    }
                });

            }
        })
    }

    deleteRow(rowid)  
    {   
        var row = document.getElementById(rowid);
        row.parentNode.removeChild(row);
    }
}
