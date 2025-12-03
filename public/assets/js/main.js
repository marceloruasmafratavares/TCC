function login() {
    const email = document.getElementById("email").value;
    const senha = document.getElementById("senha").value;

    const formData = new FormData();
    formData.append("email", email);
    formData.append("senha", senha);

    fetch("../backend/login.php", {
        method: "POST",
        body: formData
    })
    .then(r => r.text())
    .then(resp => {
        if (resp === "OK") {
            window.location.href = "dashboard.php";
        } else if (resp === "SENHA_ERRADA") {
            msg.innerHTML = "Senha incorreta!";
        } else if (resp === "EMAIL_INEXISTE") {
            msg.innerHTML = "Email não cadastrado!";
        } else {
            msg.innerHTML = "Erro inesperado.";
        }
    });
}
