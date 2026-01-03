document.querySelectorAll(".sidebar a").forEach(link=>{
  link.addEventListener("click",()=>{
    document.querySelectorAll(".sidebar a")
      .forEach(l=>l.classList.remove("active"));
    link.classList.add("active");
  });
});
