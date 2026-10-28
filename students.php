<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Students List</title>

    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 40px;
            background: #f4f6f9;
        }

        h1 {
            text-align: center;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            background: white;
            margin-top: 20px;
        }

        th, td {
            padding: 12px;
            border: 1px solid #ddd;
            text-align: left;
        }

        th {
            background: #222;
            color: white;
        }

        tr:nth-child(even) {
            background: #f2f2f2;
        }

        #message {
            text-align: center;
            margin-top: 20px;
        }
    </style>
</head>

<body>

    <h1>Students Management</h1>

    <p id="message">Loading students...</p>

    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Name</th>
                <th>Email</th>
                <th>Course</th>
                <th>Created At</th>
                <th>Action</th>

                
            </tr>
        </thead>

        <tbody id="studentsTable">
        </tbody>
    </table>
    <div id="editModal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); justify-content: center; align-items: center; z-index: 1000;">
        <div style="background: white; padding: 20px; border-radius: 8px; width: 300px;">
            <h2 style="margin-top: 0;">Edit Student</h2>
            
            <input type="hidden" id="editId">
            
            <label>Name:</label><br>
            <input type="text" id="editName" style="width: 100%; margin-bottom: 10px;"><br>
            
            <label>Email:</label><br>
            <input type="email" id="editEmail" style="width: 100%; margin-bottom: 10px;"><br>
            
            <label>Course:</label><br>
            <input type="text" id="editCourse" style="width: 100%; margin-bottom: 20px;"><br>
            
            <button onclick="saveEdit()" style="background: green; color: white; padding: 8px 15px; border: none; border-radius: 4px; cursor: pointer;">Save Changes</button>
            <button onclick="document.getElementById('editModal').style.display='none'" style="background: gray; color: white; padding: 8px 15px; border: none; border-radius: 4px; cursor: pointer;">Cancel</button>
        </div>
    </div>
    <script>
        const apiURL = "http://localhost/api/index.php";

        async function loadStudents() {
            const message = document.getElementById("message");
            const tableBody = document.getElementById("studentsTable");

            try {
                const response = await fetch(apiURL);

                const result = await response.json();

                if (!response.ok || !result.success) {
                    throw new Error(
                        result.message || "Failed to load students"
                    );
                }

                tableBody.replaceChildren();

                if (result.data.length === 0) {
                    message.textContent = "No students found.";
                    return;
                }

                              result.data.forEach(student => {
                    const row = document.createElement("tr");

                    const values = [
                        student.id,
                        student.name,
                        student.email,
                        student.course,
                        student.created_at
                    ];

                    values.forEach(value => {
                        const cell = document.createElement("td");
                        cell.textContent = value ?? "";
                        row.appendChild(cell);
                    });

                    const actionCell = document.createElement("td");

                    const editBtn = document.createElement("button");
                    editBtn.textContent = "Edit";
                    editBtn.style.marginRight = "5px";
                    editBtn.style.padding = "5px 10px";
                    editBtn.style.cursor = "pointer";
                    
                    editBtn.onclick = () => {
                        document.getElementById("editId").value = student.id;
                        document.getElementById("editName").value = student.name;
                        document.getElementById("editEmail").value = student.email;
                        document.getElementById("editCourse").value = student.course;
                        
                        document.getElementById("editModal").style.display = "flex";
                    };

                    const deleteBtn = document.createElement("button");
                    deleteBtn.textContent = "Delete";
                    deleteBtn.style.backgroundColor = "#ff4d4d";
                    deleteBtn.style.color = "white";
                    deleteBtn.style.padding = "5px 10px";
                    deleteBtn.style.border = "none";
                    deleteBtn.style.cursor = "pointer";
                    deleteBtn.style.borderRadius = "3px";

                    deleteBtn.onclick = async () => {
                        if (confirm(`Are you sure you want to delete student: ${student.name}?`)) {
                            try {
                                const res = await fetch(`${apiURL}?id=${student.id}`, {
                                    method: 'DELETE'
                                });
                                const delResult = await res.json();
                                
                                if (delResult.success) {
                                    alert("Deleted successfully!");
                                    loadStudents();
                                } else {
                                    alert("Delete failed: " + delResult.message);
                                }
                            } catch (err) {
                                console.error("Delete error:", err);
                                alert("An error occurred while trying to delete");
                            }
                        }
                    };

                    actionCell.appendChild(editBtn);
                    actionCell.appendChild(deleteBtn);

                    row.appendChild(actionCell);

                    tableBody.appendChild(row);
                });

                message.textContent =
                    `Total students: ${result.count}`;

            } catch (error) {
                message.textContent =
                    "Error loading students: " + error.message;

                console.error(error);
            }
        }

        loadStudents();


        async function saveEdit() {
            const id = document.getElementById("editId").value;
            const name = document.getElementById("editName").value;
            const email = document.getElementById("editEmail").value;
            const course = document.getElementById("editCourse").value;

            try {
                const response = await fetch(`${apiURL}?id=${id}`, {
                    method: 'PUT', 
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({ name, email, course })
                });

                const result = await response.json();

                if (result.success) {
                    alert("Updated successfully!");
                    document.getElementById("editModal").style.display = "none";
                    loadStudents(); 
                } else {
                    alert("Update failed: " + result.message);
                }
            } catch (error) {
                console.error("Update error:", error);
                alert("An error occurred while updating");
            }
        }
    </script>

</body>
</html>