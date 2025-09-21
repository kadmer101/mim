## Project Summary: Dynamic Web Components API with Standardized WebBloc

### Project Overview

This project aims to create a Laravel-based backend that provides dynamic web components as an API for static websites. The system will allow static website owners to subscribe, receive API keys, and integrate dynamic content such as authentication, comments, reviews, and other web components into their static sites. All web components will adhere to a standardized format called `WebBloc`, which includes fields like `type`, `attributes`, `CRUD`, and more.

### Key Features

1. **Central MySQL Database (100MB)**
   - **Users Table**: Stores central user authentication data, including API keys.
   - **Websites Table**: Stores metadata about each static website, including the path to its local SQLite database.
   - **Website Statistics Table**: Stores statistics and usage data for each website.
   - **WebBloc Table**: Stores the definition and metadata of all web components, including `type`, `attributes`, `CRUD`, and `metadata`.

2. **Local SQLite Databases**
   - Each static website will have its own SQLite database, named with a prefix of the website ID.
   - **Users Table**: Stores website-specific user data.
   - **WebBlocs Table**: Stores web components specific to the website, referencing the `WebBloc` table in the central MySQL database.

3. **Laravel Framework**
   - **Laravel Breeze**: Used for authentication and a clean starting point.
   - **Laravel Sanctum**: Provides token-based authentication for API requests.
   - **Intervention Image**: For image manipulation if needed.
   - **Spatie Laravel Permission**: For role and permission management.
   - **Maatwebsite Excel**: For exporting data to Excel if needed.

4. **API Endpoints (WebBloc Standard)**
   - **Authentication**:
     - `POST /api/auth/register`: User registration.
     - `POST /api/auth/login`: User login.
     - `POST /api/auth/logout`: User logout.
     - `POST /api/auth/password/email`: Password reset email.
     - `POST /api/auth/password/reset`: Password reset.
   - **WebBloc CRUD**:
     - `GET /api/webblocs/{type}`: List all web components of a specific type.
     - `POST /api/webblocs/{type}`: Create a new web component.
     - `GET /api/webblocs/{type}/{id}`: Get a specific web component.
     - `PUT /api/webblocs/{type}/{id}`: Update a web component.
     - `DELETE /api/webblocs/{type}/{id}`: Delete a web component.

5. **Dynamic SQLite Connection**
   - A service to dynamically connect to the appropriate SQLite database based on the website ID, ensuring data isolation and security.

6. **Alpine.js Integration (WebBloc Standard)**
   - Alpine.js is used for handling dynamic content on the client side, providing a lightweight alternative to Vue.js.
   - Components for authentication, comments, and reviews are created using Alpine.js and adhere to the WebBloc standard.
   - **Example Component Structure**:
     ```html
     <div x-data="webBlocComponent('comment')" class="w2030b-comment">
         <template x-if="loading">Loading...</template>
         <template x-if="!loading">
             <div x-for="comment in comments" :key="comment.id">
                 <p x-text="comment.content"></p>
                 <button @click="deleteComment(comment.id)">Delete</button>
             </div>
             <form @submit.prevent="addComment">
                 <textarea x-model="newComment.content"></textarea>
                 <button type="submit">Add Comment</button>
             </form>
         </template>
     </div>

     <script>
     function webBlocComponent(type) {
         return {
             type: type,
             loading: true,
             comments: [],
             newComment: { content: '' },
             init() {
                 this.fetchComments();
             },
             fetchComments() {
                 fetch(`/api/webblocs/${this.type}`)
                     .then(response => response.json())
                     .then(data => {
                         this.comments = data;
                         this.loading = false;
                     });
             },
             addComment() {
                 fetch(`/api/webblocs/${this.type}`, {
                     method: 'POST',
                     headers: { 'Content-Type': 'application/json' },
                     body: JSON.stringify(this.newComment)
                 })
                 .then(response => response.json())
                 .then(data => {
                     this.comments.push(data);
                     this.newComment.content = '';
                 });
             },
             deleteComment(id) {
                 fetch(`/api/webblocs/${this.type}/${id}`, { method: 'DELETE' })
                     .then(() => {
                         this.comments = this.comments.filter(comment => comment.id !== id);
                     });
             }
         }
     }
     </script>
     ```

7. **Subscription and Dashboard**
   - Static website owners subscribe to the service and receive API keys after validation and verification.
   - A dashboard is provided for managing API keys, CDN scripts, and web components.

8. **CDN Integration**
   - CDN links for JavaScript and CSS files that can be included in static websites to load dynamic components.
   - Static websites will include CDN scripts to fetch and render web components.
   - **Note:** CDN links for JavaScript and CSS files should not interfere with the static website's existing scripts and stylesheets. Only web components called via API with a public key will be included. There will also be a secret key for additional security.

9. **Web Components (WebBloc Standard)**
   - **Authentication**: Handles user registration, login, and password management.
   - **Comments (Type: `comment`)**: Allows users to add and display comments on static websites.
   - **Reviews (Type: `review`)**: Allows users to add and display reviews on static websites.
   - Additional components can be added, such as testimonials, reactions, social shares, and profiles.
   - **Standardized Fields**:
     - `type`: The type of web component (e.g., `comment`, `review`).
     - `attributes`: An array of attributes specific to the component (e.g., `limit`, `sort`).
     - `CRUD`: Boolean values indicating which CRUD operations are supported (e.g., `create: true`, `read: true`, `update: true`, `delete: true`).
     - `metadata`: Additional metadata for the component (e.g., `created_at`, `updated_at`).

10. **Component Integration Syntax**
    - Web components are defined in Blade components and can be inserted into static websites using the following syntax:
      ```html
      <div w2030b="[component_name]" w2030b_tags='{"limit": 10, "sort": "newest"}'>Content Loading...</div>
      ```
    - Example:
      ```html
      <div w2030b="comments" w2030b_tags='{"limit": 10, "sort": "newest"}'>Content Loading...</div>
      ```

11. **Error Handling and Validation**
    - Robust error handling and input validation to ensure data integrity and security.

12. **Performance Optimization**
    - Caching strategies and database indexing to ensure fast response times and efficient data retrieval.

13. **Security Measures**
    - API rate limiting, input sanitization, and secure storage of sensitive data to protect against common vulnerabilities.

14. **Notifications**
    - **SweetAlert**: For displaying success, warning, and error messages.
    - **Toast Notifications**: For non-intrusive notifications to users.
    - **Integration**: Notifications will be integrated into the Alpine.js components to provide feedback to users during interactions with web components.

15. **Deployment**
    - The application is designed to be deployed without SSH, ensuring ease of use for static website owners.

## Step 6: Source of Truth for User Activities

**Admins:**
- **Dashboard Access**: Admins have full access to the dashboard, where they can manage websites, API keys, components, and statistics.
- **Website Management**: Admins can create, edit, and delete websites. They can also view and manage the SQLite databases associated with each website.
- **API Key Management**: Admins generate and manage API keys for website owners. They can revoke keys and view usage statistics.
- **Component Management**: Admins can install, update, and remove web components. They ensure that components are functional and secure, adhering to the WebBloc standard.
- **Statistics Monitoring**: Admins monitor the usage and performance of the API and web components. They can generate reports and insights from the collected data.

**Static Website Owners:**
- **Subscription and API Key Reception**: Website owners subscribe to the service and receive API keys after validation and verification.
- **Dashboard Access**: Owners can access a limited dashboard to manage their API keys, CDN scripts, and web components.
- **Component Integration**: Owners integrate web components into their static websites using the provided CDN links and API keys.
- **Usage Monitoring**: Owners can view statistics and usage data for their websites, helping them understand the impact of the integrated components.

**Static Website Public Users:**
- **Component Interaction**: Public users can interact with the web components integrated into the static websites, such as leaving comments or reviews.
- **Anonymous Access**: Public users do not need to authenticate to interact with most components, but some components may require authentication.

**Authenticated Users of Static Websites:**
- **Component Interaction with Authentication**: Authenticated users can perform CRUD operations on components that require authentication, such as updating or deleting their comments.
- **Personalized Experience**: Authenticated users may have a personalized experience with the components, such as seeing their own data or receiving notifications.

This source of truth ensures that all user activities are clearly defined and understood, facilitating a smooth development and user experience process.
