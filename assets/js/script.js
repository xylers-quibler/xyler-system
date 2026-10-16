function filterCourses(dept) {
    let sel = document.getElementById("course"); if(!sel) return;
    sel.innerHTML = '<option value="">Select...</option>';
    allCourses.forEach(c => { if(c.department == dept) sel.innerHTML += `<option value="${c.code}">${c.code} - ${c.name}</option>`; });
}

function previewPic(input) { 
    if(input.files && input.files[0]) { 
        var r = new FileReader(); 
        r.onload = e => { document.getElementById('picPreview').src = e.target.result; }; 
        r.readAsDataURL(input.files[0]); 
    } 
}

function toggleFields() {
    let r = document.getElementById("role_id"); if(!r) return; let role = r.value;
    let dg=document.getElementById("department_group"), cg=document.getElementById("course_group"), yg=document.getElementById("year_level_group");
    dg.style.display="none"; cg.style.display="none"; yg.style.display="none";
    if(role=="2"){dg.style.display="block";cg.style.display="block";yg.style.display="block";}
    else if(role=="3"||role=="5"){dg.style.display="block";}
}

window.onload = function() { 
    if(document.getElementById('department')) toggleFields(); 
    if(document.getElementById('department')) filterCourses(document.getElementById('department').value || ''); 
};
