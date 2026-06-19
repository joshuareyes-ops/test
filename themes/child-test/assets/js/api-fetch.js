document.addEventListener("DOMContentLoaded", async function() {
    const container = document.getElementById("custom-posts-container");

    if (!container) return;

    const endpoint = wpApiSettings.root + 'mytheme/v1/filtered-posts';

    try {
        // 1. Await the network request
        const response = await fetch(endpoint, {
            method: 'GET',
            headers: {
                'X-WP-Nonce': wpApiSettings.nonce
            }
        });

        if (!response.ok) {
            throw new Error('Network response was not ok');
        }

        // 2. Await the JSON parsing
        const data = await response.json();

        // 3. Clear container and render data
        container.innerHTML = ''; 

        data.forEach(post => {
            const postElement = document.createElement('div');
            postElement.innerHTML = `<h3><a href="${post.url}">${post.title}</a></h3>`;
            container.appendChild(postElement);
        });

    } catch (error) {
        // This catches any errors from the fetch or the response check
        console.error('Fetch error:', error);
        container.innerHTML = 'Failed to load posts.';
    }
});