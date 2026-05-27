(() => {
  const path = window.location.pathname.replace(/\/+$/, "") || "/";
  document.querySelectorAll(".menu a").forEach((a) => {
    const href = a.getAttribute("href");
    if (!href) return;
    try {
      const url = new URL(href, window.location.origin);
      const clean = url.pathname.replace(/\/+$/, "") || "/";
      if (path === clean || (clean !== "/" && path.startsWith(clean + "/"))) {
        a.classList.add("active");
      }
    } catch (_) {
      // ignore malformed links
    }
  });

  const sidebarToggle = document.getElementById("sidebarToggle");
  const appShell = document.querySelector(".app-shell");
  if (sidebarToggle && appShell) {
    sidebarToggle.addEventListener("click", () => {
      if (window.innerWidth > 1024) {
        appShell.classList.toggle("sidebar-collapsed");
      } else {
        appShell.classList.toggle("sidebar-open");
      }
    });
  }

  // Theme Toggle
  const themeToggle = document.getElementById("themeToggle");
  const body = document.body;
  const themeIcon = themeToggle?.querySelector(".theme-icon");

  const updateThemeUI = (isDark) => {
    if (themeIcon) {
      themeIcon.innerHTML = isDark ? '<i class="fa-solid fa-moon"></i>' : '<i class="fa-solid fa-sun"></i>';
    }
  };

  if (themeToggle) {
    const savedTheme = localStorage.getItem("theme");
    if (savedTheme === "dark") {
      body.classList.add("dark-theme");
      updateThemeUI(true);
    }

    themeToggle.addEventListener("click", () => {
      const isDark = body.classList.toggle("dark-theme");
      localStorage.setItem("theme", isDark ? "dark" : "light");
      updateThemeUI(isDark);
    });
  }

  const ensureRemoveButtons = (rows) => {
    if (!rows) return;
    const rowList = [...rows.querySelectorAll(".item-row")];
    rowList.forEach((row, idx) => {
      let removeBtn = row.querySelector(".remove-line");
      if (!removeBtn) {
        removeBtn = document.createElement("button");
        removeBtn.type = "button";
        removeBtn.className = "btn btn-outline btn-sm remove-line";
        removeBtn.textContent = "Retirer";
        row.appendChild(removeBtn);
      }
      removeBtn.style.display = rowList.length > 1 && idx > 0 ? "inline-flex" : "none";
    });
  };

  document.querySelectorAll(".item-rows").forEach((rows) => ensureRemoveButtons(rows));

  document.querySelectorAll(".add-line").forEach((btn) => {
    btn.addEventListener("click", () => {
      const form = btn.closest("form");
      const rows = form?.querySelector(".item-rows");
      const firstRow = rows?.querySelector(".item-row");
      if (!rows || !firstRow) return;

      const clone = firstRow.cloneNode(true);
      clone.querySelectorAll("input").forEach((input) => {
        input.value = "";
      });
      clone.querySelectorAll("textarea").forEach((textarea) => {
        textarea.value = "";
      });
      clone.querySelectorAll("select").forEach((select) => {
        select.selectedIndex = 0;
      });

      rows.appendChild(clone);
      ensureRemoveButtons(rows);
    });
  });

  document.addEventListener("click", (event) => {
    const target = event.target;
    if (!(target instanceof Element) || !target.classList.contains("remove-line")) {
      return;
    }

    const row = target.closest(".item-row");
    const rows = target.closest(".item-rows");
    if (!row || !rows) return;

    if (rows.querySelectorAll(".item-row").length <= 1) return;
    row.remove();
    ensureRemoveButtons(rows);
  });

  document.addEventListener("change", (event) => {
    const target = event.target;
    if (target.name === "product_id[]" && target.tagName.toLowerCase() === "select") {
      const option = target.options[target.selectedIndex];
      const price = option.getAttribute("data-price");
      const row = target.closest(".item-row");
      if (row && price) {
        const priceInput = row.querySelector("input[name=\"unit_price[]\"]");
        if (priceInput && !priceInput.value) {
          priceInput.value = price;
        }
      }
    }
  });
})();
