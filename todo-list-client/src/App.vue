<template>
  <div class="container todo-list-container">
    <h1>Todo List</h1>
    <div class="form-group">
      <input class="form-control" v-model="newTask.title" type="text" placeholder="Title">
      <input class="form-control" v-model="newTask.deadline" type="date" placeholder="Deadline">
      <button class="btn btn-primary" @click="addTask">Add Task</button>
    </div>
    <div class="completed-tasks m-4">
      <h2>Tasks Lists</h2>
      <table class="table mt-4">
        <thead>
          <tr>
            <th>NO</th>
            <th>Title</th>
            <th>Deadline</th>
            <th>Completed</th>
            <th>Remove</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="(task, index) in tasks" :key="task.id">
            <td>{{ task.id }}</td>
            <td>{{ task.title }}</td>
            <td>{{ task.deadline }}</td>
            <td><input type="checkbox" class="form-check" @change="moveCompletedTask(index)" :checked="convertStatusToBoolean(task.status)"
                v-bind:id="task.id">
            </td>
            <td>
              <button class="btn btn-primary" @click="removeTask(index)">Remove</button>
            </td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>
</template>

<script>
export default {
  data() {
    return {
      newTask: {
        title: '',
        deadline: '',
        status: 0
      },
      tasks: []
    };
  },

  mounted() {
    this.fetchTasks();
  },
  methods: {
    convertStatusToBoolean(status) {
      return status === 1;
    },

    fetchTasks() {
      fetch('http://127.0.0.1:8000/api/todo/list')
        .then(response => {
          if (!response.ok) {
            throw new Error('Network response was not ok');
          }
          return response.json();
        })
        .then(data => {
          this.tasks = data.lists;
          console.log(data.lists);
          this.tasks.sort((a, b) => a.status - b.status);
        })
        .catch(error => {
          console.error('Error fetching tasks:', error);
        });
    },
    addTask() {
      if (this.newTask.title && this.newTask.deadline) {
        const formData = new URLSearchParams();
        formData.append('title', this.newTask.title);
        formData.append('deadline', this.newTask.deadline);
        formData.append('status', 0); // Assuming status is always 0

        // Make a POST request to add the new task to the API
        fetch('http://127.0.0.1:8000/api/todo/list/create', {
          method: 'POST',
          headers: {
            'Content-Type': 'application/x-www-form-urlencoded'
          },
          body: formData
        })
          .then(response => {
            if (!response.ok) {
              throw new Error('Failed to add task');
            }
            return response.json();
          })
          .then(data => {
            // Update local state with the new task returned from the API
            this.tasks.unshift(data);
            // Reset the newTask object
            this.newTask.title = '';
            this.newTask.deadline = '';
            this.newTask.status = 0; // Assuming status is false by default
          })
          .catch(error => {
            console.error('Error adding task:', error);
          });
      }
    },
    removeTask(index) {
      const taskId = this.tasks[index].id; // Assuming tasks have unique IDs

      // Make a DELETE request to the API endpoint to remove the task
      fetch(`http://127.0.0.1:8000/api/todo/list/${taskId}/delete`, {
        method: 'DELETE'
      })
        .then(response => {
          console.log(response);
          if (!response.ok) {
            throw new Error('Failed to remove task');
          }
          // If the request is successful, remove the task from the local array
          this.tasks.splice(index, 1);
        })
        .catch(error => {
          console.error('Error removing task:', error);
        });
    },
    moveCompletedTask(index) {
      const task = this.tasks[index];
      const newStatus = task.status ? 0 : 1;
      console.log('here itsl', task, newStatus);

      // Update the local task status immediately for a better user experience
      task.status = newStatus

      const formData = new URLSearchParams();
      formData.append('status', newStatus); // Assuming status is always 0

      // Send a PATCH request to update the task's completion status on the server
      fetch(`http://127.0.0.1:8000/api/todo/list/${task.id}/status`, {
        method: 'POST',
        headers: {
          'Content-Type': 'application/x-www-form-urlencoded'
        },
        body: formData
      })
        .then(response => {
          if (!response.ok) {
            throw new Error('Failed to update task status on server');
          }
          // Handle success if needed
          if (task.status) {
            this.tasks.splice(index, 1);
            this.tasks.push(task);
          }
          this.tasks.sort((a, b) => a.status - b.status);
        })
        .catch(error => {
          console.error('Error updating task status:', error);
          // Roll back the local task status on error
          task.completed = !newStatus;
        });
    }

  }
};
</script>
