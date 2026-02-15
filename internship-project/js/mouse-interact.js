/**
 * Mouse Flee Interaction for Stationery Icons
 * Makes icons move away from cursor
 */

$(document).ready(function() {
    $(document).on('mousemove', function(e) {
        $('.item').each(function() {
            const item = $(this);
            const rect = item[0].getBoundingClientRect();
            const centerX = rect.left + rect.width / 2;
            const centerY = rect.top + rect.height / 2;

            const dx = e.clientX - centerX;
            const dy = e.clientY - centerY;
            const dist = Math.sqrt(dx*dx + dy*dy);

            // If mouse is within 180px, push the item away
            if (dist < 180) {
                const angle = Math.atan2(dy, dx);
                const force = (180 - dist) / 6;
                const x = -Math.cos(angle) * force;
                const y = -Math.sin(angle) * force;
                item.css('transform', `translate(${x}px, ${y}px) rotate(${x * 0.5}deg)`);
            } else {
                item.css('transform', 'translate(0, 0) rotate(0deg)');
            }
        });
    });
});
