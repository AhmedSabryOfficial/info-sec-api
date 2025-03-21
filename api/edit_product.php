<?php
?>
<!DOCTYPE html>
<html lang="ar">
<head>
  <meta charset="UTF-8">
  <title>إدارة المنتجات</title>
  <style>
    body { font-family: Arial, sans-serif; margin: 20px; background: #f9f9f9; }
    nav { text-align: center; margin-bottom: 20px; }
    nav button { padding: 10px 15px; margin: 5px; background: #f44336; color: #fff; border: none; border-radius: 4px; cursor: pointer; }
    nav button:hover { background: #d32f2f; }
    h2 { text-align: center; }
    form { max-width: 500px; margin: auto; background: #fff; padding: 20px; border-radius: 8px; box-shadow: 0 0 10px rgba(0,0,0,0.1); }
    form input, form textarea { width: 100%; padding: 8px; margin-bottom: 15px; border: 1px solid #ccc; border-radius: 4px; }
    form button { padding: 10px; width: 100%; background-color: #4CAF50; color: white; border: none; border-radius: 4px; cursor: pointer; }
    form button:hover { background-color: #45a049; }
    #message { text-align: center; font-weight: bold; margin-top: 20px; }
    table { width: 100%; border-collapse: collapse; margin-top: 20px; }
    table, th, td { border: 1px solid #ccc; }
    th, td { padding: 10px; text-align: center; }
    .action-btn { margin: 0 5px; padding: 5px 10px; cursor: pointer; border: none; border-radius: 4px; }
    .edit-btn { background-color: #2196F3; color: white; }
    .delete-btn { background-color: #f44336; color: white; }
  </style>
</head>
<body>
  <nav>
    <button id="logout">تسجيل الخروج</button>
  </nav>

  <h2>إدارة المنتجات</h2>

  <div id="productListDiv">
    <h3>قائمة المنتجات</h3>
    <table id="productTable">
      <thead>
        <tr>
          <th>المعرف</th>
          <th>اسم المنتج</th>
          <th>الوصف</th>
          <th>السعر</th>
          <th>المخزون</th>
          <th>الإجراءات</th>
        </tr>
      </thead>
      <tbody>
      </tbody>
    </table>
  </div>

  <div id="addProductDiv">
    <h3>إضافة منتج جديد</h3>
    <form id="addProductForm">
      <label for="add_pname">اسم المنتج:</label>
      <input type="text" id="add_pname" name="pname" required>
      <label for="add_description">الوصف:</label>
      <textarea id="add_description" name="description" rows="3"></textarea>
      <label for="add_price">السعر:</label>
      <input type="number" id="add_price" name="price" step="0.01" required>
      <label for="add_stock">المخزون:</label>
      <input type="number" id="add_stock" name="stock" required>
      <button type="submit">إضافة المنتج</button>
    </form>
  </div>

  <div id="editProductDiv" style="display:none;">
    <h3>تعديل المنتج</h3>
    <form id="editProductForm">
      <input type="hidden" id="edit_pid">
      <label for="edit_pname">اسم المنتج:</label>
      <input type="text" id="edit_pname" name="pname" required>
      <label for="edit_description">الوصف:</label>
      <textarea id="edit_description" name="description" rows="3"></textarea>
      <label for="edit_price">السعر:</label>
      <input type="number" id="edit_price" name="price" step="0.01" required>
      <label for="edit_stock">المخزون:</label>
      <input type="number" id="edit_stock" name="stock" required>
      <button type="submit">تحديث المنتج</button>
      <button type="button" id="cancelEdit" style="background-color:#999; margin-top:10px;">إلغاء</button>
    </form>
  </div>

  <div id="message"></div>

  <script>
    const token = localStorage.getItem("token");
    if (!token) {
      alert("يرجى تسجيل الدخول أولاً");
      window.location.href = "../login.html";
    }

    document.getElementById("logout").addEventListener("click", function(){
      localStorage.removeItem("token");
      window.location.href = "../login.html";
    });

    const apiBase = "product.php";

    function loadProducts() {
      fetch(apiBase, {
        headers: { "Authorization": "Bearer " + token }
      })
      .then(res => res.json())
      .then(data => {
        const tbody = document.querySelector("#productTable tbody");
        tbody.innerHTML = "";
        if(Array.isArray(data)) {
          data.forEach(product => {
            const tr = document.createElement("tr");
            tr.innerHTML = `
              <td>${product.pid}</td>
              <td>${product.pname}</td>
              <td>${product.description}</td>
              <td>${product.price}</td>
              <td>${product.stock}</td>
              <td>
                <button class="action-btn edit-btn" data-id="${product.pid}">تعديل</button>
                <button class="action-btn delete-btn" data-id="${product.pid}">حذف</button>
              </td>
            `;
            tbody.appendChild(tr);
          });
        } else {
          document.getElementById("message").innerText = data.error || "لا توجد منتجات";
        }
      })
      .catch(err => {
        console.error(err);
        document.getElementById("message").innerText = "حدث خطأ أثناء تحميل قائمة المنتجات";
      });
    }

    loadProducts();

    document.getElementById("addProductForm").addEventListener("submit", function(e) {
      e.preventDefault();
      const pname = document.getElementById("add_pname").value;
      const description = document.getElementById("add_description").value;
      const price = document.getElementById("add_price").value;
      const stock = document.getElementById("add_stock").value;
      
      fetch(apiBase, {
        method: "POST",
        headers: {
          "Content-Type": "application/json",
          "Authorization": "Bearer " + token
        },
        body: JSON.stringify({ pname, description, price, stock })
      })
      .then(res => res.json())
      .then(data => {
        const messageDiv = document.getElementById("message");
        if(data.message) {
          messageDiv.style.color = "green";
          messageDiv.innerText = data.message;
          document.getElementById("addProductForm").reset();
          loadProducts();
        } else if(data.error) {
          messageDiv.style.color = "red";
          messageDiv.innerText = data.error;
        }
      })
      .catch(error => {
        console.error("Error:", error);
        document.getElementById("message").innerText = "حدث خطأ أثناء إضافة المنتج.";
      });
    });

    document.querySelector("#productTable tbody").addEventListener("click", function(e) {
      if(e.target.classList.contains("edit-btn")) {
        const pid = e.target.getAttribute("data-id");
        fetch(apiBase + "?pid=" + pid, {
          headers: { "Authorization": "Bearer " + token }
        })
        .then(res => res.json())
        .then(data => {
          if(data.error) {
            document.getElementById("message").innerText = data.error;
          } else {
            document.getElementById("edit_pid").value = data.pid;
            document.getElementById("edit_pname").value = data.pname;
            document.getElementById("edit_description").value = data.description;
            document.getElementById("edit_price").value = data.price;
            document.getElementById("edit_stock").value = data.stock;
            document.getElementById("editProductDiv").style.display = "block";
          }
        })
        .catch(err => {
          console.error(err);
          document.getElementById("message").innerText = "حدث خطأ أثناء تحميل بيانات المنتج للتعديل";
        });
      }
      else if(e.target.classList.contains("delete-btn")) {
        const pid = e.target.getAttribute("data-id");
        if(confirm("هل أنت متأكد من حذف هذا المنتج؟")) {
          fetch(apiBase + "?pid=" + pid, {
            method: "DELETE",
            headers: { "Authorization": "Bearer " + token }
          })
          .then(res => res.json())
          .then(data => {
            const messageDiv = document.getElementById("message");
            if(data.message) {
              messageDiv.style.color = "green";
              messageDiv.innerText = data.message;
              loadProducts();
            } else if(data.error) {
              messageDiv.style.color = "red";
              messageDiv.innerText = data.error;
            }
          })
          .catch(err => {
            console.error(err);
            document.getElementById("message").innerText = "حدث خطأ أثناء حذف المنتج";
          });
        }
      }
    });

    document.getElementById("editProductForm").addEventListener("submit", function(e){
      e.preventDefault();
      const pid = document.getElementById("edit_pid").value;
      const pname = document.getElementById("edit_pname").value;
      const description = document.getElementById("edit_description").value;
      const price = document.getElementById("edit_price").value;
      const stock = document.getElementById("edit_stock").value;
      
      fetch(apiBase + "?pid=" + pid, {
        method: "PUT",
        headers: {
          "Content-Type": "application/json",
          "Authorization": "Bearer " + token
        },
        body: JSON.stringify({ pname, description, price, stock })
      })
      .then(res => res.json())
      .then(data => {
        const messageDiv = document.getElementById("message");
        if(data.message) {
          messageDiv.style.color = "green";
          messageDiv.innerText = data.message;
          document.getElementById("editProductDiv").style.display = "none";
          loadProducts();
        } else if(data.error) {
          messageDiv.style.color = "red";
          messageDiv.innerText = data.error;
        }
      })
      .catch(error => {
        console.error("Error:", error);
        document.getElementById("message").innerText = "حدث خطأ أثناء تحديث المنتج.";
      });
    });

    document.getElementById("cancelEdit").addEventListener("click", function(){
      document.getElementById("editProductDiv").style.display = "none";
    });
  </script>
</body>
</html>
