<x-admin-layout>
    <div class="container mt-4">
        <h1 class="mb-4">Dashboard</h1>

        <div class="row">
            <!-- Users Card -->
            <div class="col-md-4">
                <div class="card text-white bg-primary mb-3">
                    <div class="card-body">
                        <h5 class="card-title">Users</h5>
                        <p class="card-text display-4">120</p>
                    </div>
                </div>
            </div>

            <!-- Tim Card -->
            <div class="col-md-4">
                <div class="card text-white bg-success mb-3">
                    <div class="card-body">
                        <h5 class="card-title">Tim</h5>
                        <p class="card-text display-4">8</p>
                    </div>
                </div>
            </div>

            <!-- Honor Card -->
            <div class="col-md-4">
                <div class="card text-white bg-warning mb-3">
                    <div class="card-body">
                        <h5 class="card-title">Honor</h5>
                        <p class="card-text display-4">$12k</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Recent Activity Table -->
        <div class="card mt-4">
            <div class="card-header">
                Recent Activity
            </div>
            <div class="card-body p-0">
                <table class="table mb-0">
                    <thead class="thead-light">
                        <tr>
                            <th>User</th>
                            <th>Action</th>
                            <th>Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>Alice</td>
                            <td>Logged in</td>
                            <td>2025-08-23</td>
                        </tr>
                        <tr>
                            <td>Bob</td>
                            <td>Created Post</td>
                            <td>2025-08-22</td>
                        </tr>
                        <tr>
                            <td>Charlie</td>
                            <td>Deleted Comment</td>
                            <td>2025-08-21</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

    </div>
</x-admin-layout>
