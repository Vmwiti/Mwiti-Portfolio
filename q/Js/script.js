const fruitForm = document.getElementById('fruitForm');
const fruitTableBody = document.getElementById('fruitTableBody');

let fruits = [];

fruitForm.addEventListener('submit', function (e) {
  e.preventDefault();

  const name = document.getElementById('name').value;
  const quantity = parseInt(document.getElementById('quantity').value);
  const price = parseFloat(document.getElementById('price').value);

  const fruit = { name, quantity, price };
  fruits.push(fruit);
  displayFruits();
  fruitForm.reset();
});

function displayFruits() {
  fruitTableBody.innerHTML = '';
  fruits.forEach((fruit, index) => {
    const row = document.createElement('tr');

    row.innerHTML = `
      <td>${fruit.name}</td>
      <td>${fruit.quantity}</td>
      <td>$${fruit.price.toFixed(2)}</td>
      <td><button class="delete-btn" onclick="deleteFruit(${index})">Delete</button></td>
    `;

    fruitTableBody.appendChild(row);
  });
}

function deleteFruit(index) {
  fruits.splice(index, 1);
  displayFruits();
}
