document.addEventListener('DOMContentLoaded', function() {
    const seatsGrid = document.querySelector('.seats-grid');
    const selectedSeatSpan = document.getElementById('selected-seat');
    const fareAmountSpan = document.getElementById('fare-amount');
    const proceedBtn = document.getElementById('proceed-btn');
    
    let selectedSeat = null;
    const baseFare = 500; // This should come from the backend

    // Generate seat grid
    function generateSeats() {
        for(let i = 1; i <= 28; i++) {
            const seat = document.createElement('div');
            seat.className = 'seat available';
            seat.dataset.seatNumber = i;
            seat.innerHTML = i;
            
            seat.addEventListener('click', function() {
                if(this.classList.contains('booked')) return;
                
                // Deselect previously selected seat
                if(selectedSeat) {
                    selectedSeat.classList.remove('selected');
                }
                
                // Select new seat
                this.classList.add('selected');
                selectedSeat = this;
                selectedSeatSpan.textContent = i;
                fareAmountSpan.textContent = baseFare;
                proceedBtn.disabled = false;
            });
            
            seatsGrid.appendChild(seat);
        }
    }

    generateSeats();

    // Proceed to payment
    proceedBtn.addEventListener('click', function() {
        if(!selectedSeat) return;
        
        const seatNumber = selectedSeat.dataset.seatNumber;
        window.location.href = `payment.php?seat=${seatNumber}&fare=${baseFare}`;
    });
}); 