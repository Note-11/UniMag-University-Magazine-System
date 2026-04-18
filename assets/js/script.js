let menuicn = document.querySelector(".menuicn");
let nav = document.querySelector(".navcontainer");

menuicn.addEventListener("click", () => {
    nav.classList.toggle("navclose");
});


//student upload popup
const uploadModal = document.getElementById("uploadModal");
const openUploadBtn = document.getElementById("openModalBtn");
const closeUploadBtn = document.getElementById("closeModalBtn");
const uploadForm = document.getElementById("uploadForm"); 

openUploadBtn.onclick = function() {
    uploadModal.style.display = "flex";
}

closeUploadBtn.onclick = function() {
    uploadModal.style.display = "none";
    uploadForm.reset(); 
}


//document preview popup
const previewModal = document.getElementById("documentPreviewModal");
const closePreviewBtn = document.getElementById("closePreviewBtn");
const previewBtns = document.querySelectorAll(".open-preview-btn");

previewBtns.forEach(btn => {
    btn.addEventListener("click", () => {
        previewModal.style.display = "flex";
    });
});

closePreviewBtn.onclick = function() {
    previewModal.style.display = "none";
}

window.addEventListener('click', function(event) {
    if (event.target == uploadModal) {
        uploadModal.style.display = "none";
        uploadForm.reset();
    }
    if (event.target == previewModal) {
        previewModal.style.display = "none";
    }
});