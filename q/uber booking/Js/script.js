function bookRide() {
  const pickup = document.getElementById('pickup').value;
  const drop = document.getElementById('drop').value;
  const datetime = document.getElementById('datetime').value;

  if (!pickup || !drop || !datetime) {
    alert('Please fill all the fields');
    return;
  }

  const rides = [
    { type: "UberX", price: "ksh120", time: "5 mins", img: "images/car.png" },
    { type: "UberXL", price: "ksh189", time: "6 mins", img: "images/car.png" },
    { type: "Uber Black", price: "ksh250", time: "8 mins", img: "images/car.png" },
  ];

  const rideContainer = document.getElementById("rideOptions");
  rideContainer.innerHTML = "<h2>Available Rides:</h2>";

  rides.forEach((ride) => {
    rideContainer.innerHTML += `
      <div class="ride">
        <img src="${ride.img}" alt="car icon" />
        <div class="ride-details">
          <strong>${ride.type}</strong><br/>
          Price: ${ride.price}<br/>
          Arrival in: ${ride.time}
        </div>
      </div>
    `;
  });
}
