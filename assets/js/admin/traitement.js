// Variables globales
let currentEditId = null

// Initialisation
document.addEventListener("DOMContentLoaded", () => {
  setupEventListeners()
  updateSelectedCount()
})

// Configuration des événements
function setupEventListeners() {
  const searchInput = document.getElementById("searchInput")
  let searchTimeout

  searchInput.addEventListener("input", () => {
    clearTimeout(searchTimeout)
    searchTimeout = setTimeout(() => {
      performSearch()
    }, 500)
  })

  // Fermer modal en cliquant à l'extérieur
  document.addEventListener("click", (e) => {
    if (e.target.classList.contains("modal")) {
      closeModal()
    }
  })
}

// Recherche
function performSearch() {
  const searchTerm = document.getElementById("searchInput").value
  const url = new URL(window.location)

  if (searchTerm.trim()) {
    url.searchParams.set("search", searchTerm)
  } else {
    url.searchParams.delete("search")
  }
  url.searchParams.set("page", "1") // Retour à la page 1

  window.location.href = url.toString()
}

function clearSearch() {
  document.getElementById("searchInput").value = ""
  const url = new URL(window.location)
  url.searchParams.delete("search")
  url.searchParams.set("page", "1")
  window.location.href = url.toString()
}

// Gestion de la sélection
function toggleSelectAll() {
  const selectAll = document.getElementById("selectAll") || document.getElementById("selectAllHeader")
  const checkboxes = document.querySelectorAll(".row-checkbox")

  checkboxes.forEach((checkbox) => {
    checkbox.checked = selectAll.checked
  })

  // Synchroniser les deux checkboxes "Tout sélectionner"
  const otherSelectAll =
    selectAll.id === "selectAll" ? document.getElementById("selectAllHeader") : document.getElementById("selectAll")
  if (otherSelectAll) {
    otherSelectAll.checked = selectAll.checked
  }

  updateSelectedCount()
}

function updateSelectedCount() {
  const selected = document.querySelectorAll(".row-checkbox:checked")
  const count = selected.length
  const total = document.querySelectorAll(".row-checkbox").length

  document.getElementById("selectedCount").textContent = `${count} sélectionné(s)`

  // Afficher/masquer le bouton de suppression
  const deleteBtn = document.getElementById("deleteSelected")
  if (count > 0) {
    deleteBtn.style.display = "inline-flex"
  } else {
    deleteBtn.style.display = "none"
  }

  // Mettre à jour l'état des checkboxes "Tout sélectionner"
  const selectAllCheckboxes = [document.getElementById("selectAll"), document.getElementById("selectAllHeader")]
  selectAllCheckboxes.forEach((checkbox) => {
    if (checkbox) {
      checkbox.checked = count === total && total > 0
      checkbox.indeterminate = count > 0 && count < total
    }
  })
}

// CRUD Operations
function openAddModal() {
  currentEditId = null
  document.getElementById("modalTitle").textContent = "Ajouter un traitement"
  document.getElementById("traitementForm").reset()
  document.getElementById("formAction").value = "add"
  document.getElementById("traitementModal").classList.add("active")
  document.getElementById("lib_trait").focus()
}

function editTraitement(id, libelle) {
  currentEditId = id
  document.getElementById("modalTitle").textContent = "Modifier le traitement"
  document.getElementById("traitementId").value = id
  document.getElementById("lib_trait").value = libelle
  document.getElementById("formAction").value = "edit"
  document.getElementById("traitementModal").classList.add("active")
  document.getElementById("lib_trait").focus()
}

function saveTraitement() {
  const form = document.getElementById("traitementForm")
  const formData = new FormData(form)

  // Validation
  const libTrait = formData.get("lib_trait").trim()
  if (!libTrait) {
    showNotification("Veuillez saisir le libellé du traitement", "error")
    return
  }

  // Envoi des données
  fetch("../../classes/controller.php", {
    method: "POST",
    body: formData,
  })
    .then((response) => response.json())
    .then((data) => {
      if (data.success) {
        showNotification(data.message, "success")
        closeModal()
        setTimeout(() => {
          window.location.reload()
        }, 1000)
      } else {
        showNotification(data.message, "error")
      }
    })
    .catch((error) => {
      showNotification("Erreur de connexion", "error")
      console.error("Erreur:", error)
    })
}

function deleteTraitement(id) {
  if (!confirm("Êtes-vous sûr de vouloir supprimer ce traitement ?")) {
    return
  }

  const formData = new FormData()
  formData.append("action", "delete")
  formData.append("id_trait", id)

  fetch("controller.php", {
    method: "POST",
    body: formData,
  })
    .then((response) => response.json())
    .then((data) => {
      if (data.success) {
        showNotification(data.message, "success")
        setTimeout(() => {
          window.location.reload()
        }, 1000)
      } else {
        showNotification(data.message, "error")
      }
    })
    .catch((error) => {
      showNotification("Erreur de connexion", "error")
      console.error("Erreur:", error)
    })
}

function deleteSelected() {
  const selected = Array.from(document.querySelectorAll(".row-checkbox:checked")).map((cb) => cb.value)

  if (selected.length === 0) {
    showNotification("Aucun élément sélectionné", "warning")
    return
  }

  if (!confirm(`Êtes-vous sûr de vouloir supprimer ${selected.length} traitement(s) ?`)) {
    return
  }

  const formData = new FormData()
  formData.append("action", "delete_multiple")
  selected.forEach((id) => formData.append("ids[]", id))

  fetch("controller.php", {
    method: "POST",
    body: formData,
  })
    .then((response) => response.json())
    .then((data) => {
      if (data.success) {
        showNotification(data.message, "success")
        setTimeout(() => {
          window.location.reload()
        }, 1000)
      } else {
        showNotification(data.message, "error")
      }
    })
    .catch((error) => {
      showNotification("Erreur de connexion", "error")
      console.error("Erreur:", error)
    })
}

function closeModal() {
  document.getElementById("traitementModal").classList.remove("active")
  currentEditId = null
}

// Fonctions d'export
function printData() {
  const printWindow = window.open("", "_blank")
  const table = document.getElementById("dataTable").cloneNode(true)

  // Supprimer les colonnes de sélection et d'actions
  const rows = table.querySelectorAll("tr")
  rows.forEach((row) => {
    row.deleteCell(0) // Checkbox
    if (row.cells.length > 2) {
      row.deleteCell(row.cells.length - 1) // Actions
    }
  })

  printWindow.document.write(`
        <html>
            <head>
                <title>Liste des Traitements</title>
                <style>
                    body { font-family: Arial, sans-serif; }
                    table { width: 100%; border-collapse: collapse; margin-top: 20px; }
                    th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
                    th { background-color: #f5f5f5; font-weight: bold; }
                    h1 { text-align: center; color: #333; }
                    .print-date { text-align: center; color: #666; margin-bottom: 20px; }
                </style>
            </head>
            <body>
                <h1>Liste des Traitements</h1>
                <div class="print-date">Généré le ${new Date().toLocaleDateString("fr-FR")}</div>
                ${table.outerHTML}
            </body>
        </html>
    `)

  printWindow.document.close()
  printWindow.print()
}

function exportToPDF() {
  // Redirection vers un script PHP qui génère le PDF
  window.open("export_pdf.php?type=traitements", "_blank")
}

function exportToExcel() {
  // Redirection vers un script PHP qui génère l'Excel
  window.open("export_excel.php?type=traitements", "_blank")
}

// Fonction de notification
function showNotification(message, type = "info") {
  const notification = document.createElement("div")
  notification.className = `notification notification-${type}`
  notification.innerHTML = `
        <div class="notification-content">
            <i class="fas fa-${
              type === "success"
                ? "check-circle"
                : type === "error"
                  ? "exclamation-circle"
                  : type === "warning"
                    ? "exclamation-triangle"
                    : "info-circle"
            }"></i>
            <span>${message}</span>
        </div>
        <button class="notification-close" onclick="this.parentElement.remove()">
            <i class="fas fa-times"></i>
        </button>
    `

  document.body.appendChild(notification)

  setTimeout(() => {
    if (notification.parentElement) {
      notification.remove()
    }
  }, 5000)
}

// Gestion du clavier
document.addEventListener("keydown", (e) => {
  // Escape pour fermer le modal
  if (e.key === "Escape") {
    closeModal()
  }

  // Enter pour sauvegarder dans le modal
  if (e.key === "Enter" && document.getElementById("traitementModal").classList.contains("active")) {
    e.preventDefault()
    saveTraitement()
  }
})
