document.addEventListener("DOMContentLoaded", function() {
    // Получаем все элементы с классом .attached-files-list-header
    const headers = document.querySelectorAll(".attached-files-list-header");

    // Проходимся по каждому элементу и добавляем обработчик события клика
    headers.forEach(function(header) {
        header.addEventListener("click", function() {
            const content = this.nextElementSibling;
            if (content.classList.contains("show")) {
                content.classList.remove("show");
                // Ждем завершения анимации, чтобы скрыть элемент
                setTimeout(() => {
                    content.style.display = "none";
                }, 500); // Должно совпадать с временем transition из CSS
            } else {
                content.style.display = "block";
                // Небольшая задержка для запуска анимации
                setTimeout(() => {
                    content.classList.add("show");
                }, 10);
            }
        });
    });
});
