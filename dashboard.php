<?php
session_start();
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Dashboard - Taskify</title>
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<script crossorigin src="https://unpkg.com/react@18/umd/react.development.js"></script>
<script crossorigin src="https://unpkg.com/react-dom@18/umd/react-dom.development.js"></script>
<script src="https://unpkg.com/@babel/standalone/babel.min.js"></script>

<style>
  * { box-sizing: border-box; }
  body { margin: 0; font-family: 'Plus Jakarta Sans', sans-serif; background: #f5f7fa; color: #222; line-height: 1.6; }

  /* Header */
  header { 
    background: #00b894; 
    color: white; 
    padding: 1rem 0; 
  }
  .header-logo { 
    height: 50px; 
    width: auto; 
  }
  .brand { 
    display: flex; 
    align-items: center; 
    gap: 10px; 
  }
  .header-content { 
    display: flex; 
    justify-content: space-between; 
    align-items: center; 
  }
  .container { 
    width: 90%; 
    max-width: 1200px; /* Increased max-width for better spacing */
    margin: auto; 
  }

/* CSS removed */

  /* Right Side */
  .user-actions {
    display: flex;
    align-items: center;
    gap: 1.5rem;
  }
  .welcome { 
    color: white; 
    font-weight: bold; 
  }
  .btn-logout { 
    background: #008f72; 
    color: white;
    text-decoration: none;
    font-weight: bold;
    padding: 0.5rem 1rem; 
    border-radius: 6px; 
    transition: background 0.3s;
  }
  .btn-logout:hover { 
    background-color: #007a5f; 
  }

  /* Panels */
  .panel { 
    background: white; 
    margin-top: 1.5rem; 
    padding: 1.5rem; 
    border-radius: 10px; 
    box-shadow: 0 0 10px rgba(0,0,0,0.06); 
  }
  
  /* CSS classes React will use */
  .task-form { 
    display: flex; 
    gap: 0.5rem; 
    margin-bottom: 1rem; 
  }
  .task-form input[type="text"] { 
    flex: 1; 
    padding: 0.6rem; 
    border-radius: 8px; 
    border: 1px solid #ccc; 
    font-size: 1rem; 
  }
  .task-form button { 
    border: none; 
    padding: 0.6rem 1rem; 
    border-radius: 8px; 
    cursor: pointer; 
    background: #00b894; 
    color: white; 
    font-weight: bold; 
  }
  .task-form button:hover { 
    background: #008f72; 
  }

  /* Notes/Tasks List Styling */
  .notes-list { 
    margin-top: 1rem; 
    display: grid; 
    gap: 1rem; 
  }
  .note-card { 
    background: #f0f4ff; 
    padding: 0.8rem; 
    border-radius: 8px; 
    display: flex; 
    justify-content: space-between; 
    align-items: center; }

  /* Footer */
  footer { 
    text-align: center; 
    margin-top: 2rem; 
    padding: 1rem 0; 
    color: #444; 
  }

  /* Mobile */
  /* Mobile & Tablet */
  @media(max-width: 768px) {
    .header-content { 
      flex-direction: column; 
      gap: 1rem; 
      text-align: center;
    }
    .brand {
      justify-content: center;
    }
    .brand img {
      max-height: 60px !important; /* Smaller logo on mobile */
    }
    .user-actions {
      width: 100%;
      justify-content: center;
      gap: 1rem;
    }
    .container {
      width: 95%;
    }
    .task-form {
      flex-direction: column;
    }
    .task-form button, .task-form input[type="text"] {
      width: 100%;
    }
    .note-card {
      flex-direction: column;
      align-items: flex-start;
      gap: 0.5rem;
    }
    .note-card button {
      align-self: flex-end;
    }
  }
</style>
</head>

<body>

<header>
  <div class="container header-content">
    <!-- Left: Brand -->
    <div class="brand">
      <img src="white_new.png" alt="Taskify" style="height: 80px; width: auto; max-width: 250px;">
    </div>

    <!-- Right: User Info & Logout -->
    <div class="user-actions">
      <span class="welcome">Welcome, <?php echo $_SESSION['fname'] ?? 'User'; ?></span>
      <a href="logout.php" class="btn-logout">Logout</a>
    </div>
  </div>
</header>

<main class="container">
  <section class="panel" id="tasks">
    <h2>Tasks Manager</h2>
    <div id="task-root"></div>
  </section>

  <section class="panel" id="notes">
    <h2>Notes Manager</h2>
    <div id="note-root"></div>
  </section>
</main>

<footer>
  <p>&copy; 2026 Taskify. Made by Yazan Muhsen, Yazan Zarka, Fares Alshafei, and Shahed Mishal.</p>
</footer>

<script type="text/babel">
  function TaskManager() {
    const [tasks, setTasks] = React.useState([]);
    const [inputValue, setInputValue] = React.useState("");

    const [editId, setEditId] = React.useState(null);
    const [editText, setEditText] = React.useState("");

    // Load tasks from DB on mount
    React.useEffect(() => {
      fetch('fetch.php?type=task')
        .then(res => res.json())
        .then(data => setTasks(data))
        .catch(err => console.error("Error fetching tasks:", err));
    }, []);

    const handleAddTask = async (e) => {
      e.preventDefault(); 
      if (!inputValue.trim()) return; 

      const formData = new FormData();
      formData.append('text', inputValue);
      formData.append('type', 'task');

      fetch('save.php', { method: 'POST', body: formData })
        .then(res => res.json())
        .then(data => {
            if (data.status === 'success') {
                // Refresh list from DB to get ID and correct state
                fetch('fetch.php?type=task')
                    .then(res => res.json())
                    .then(data => setTasks(data));
            } else {
                alert('Error processing request: ' + data.message);
            }
        })
        .catch(err => alert("Network/Server Error: " + err));

      // Optimistic update (optional, but clearing input is good)
      setInputValue(""); 
    };

    const toggleImportant = (task) => {
      const formData = new FormData();
      formData.append('id', task.id);
      formData.append('isImportant', task.isImportant ? 0 : 1);
      formData.append('type', 'task');

      fetch('update.php', { method: 'POST', body: formData })
        .then(res => res.json())
        .then(data => {
            if(data.status === 'success') {
                fetch('fetch.php?type=task')
                    .then(res => res.json())
                    .then(data => setTasks(data));
            }
        });
    };

    const handleMarkDone = (task) => {
      const formData = new FormData();
      formData.append('id', task.id);
      formData.append('isDone', task.isDone ? 0 : 1); 
      formData.append('type', 'task');

      fetch('update.php', { method: 'POST', body: formData })
        .then(res => res.json())
        .then(data => {
             if(data.status === 'success') {
                fetch('fetch.php?type=task')
                    .then(res => res.json())
                    .then(data => setTasks(data));
             }
        });
    };

    const handleDelete = (id) => {
      if(!confirm("Are you sure?")) return;
      
      const formData = new URLSearchParams({ id: id, type: 'task' });
      fetch('delete.php', { method: 'POST', body: formData })
        .then(res => res.json())
        .then(data => {
            if(data.status === 'success') {
                setTasks(tasks.filter(t => t.id !== id));
            } else {
                alert(data.message);
            }
        });
    };

    const startEditing = (task) => {
      setEditId(task.id);
      setEditText(task.text);
    };

    const saveEdit = (id) => {
      const formData = new FormData();
      formData.append('id', id);
      formData.append('text', editText);
      formData.append('type', 'task');

      fetch('update.php', { method: 'POST', body: formData })
        .then(res => res.json())
        .then(data => {
            if(data.status === 'success') {
                fetch('fetch.php?type=task')
                    .then(res => res.json())
                    .then(data => setTasks(data));
                setEditId(null);
            } else {
                alert(data.message);
            }
        });
    };

    // Filter tasks
    const activeTasks = tasks.filter(task => !task.isDone);
    const finishedTasks = tasks.filter(task => task.isDone);

    const sortedActiveTasks = activeTasks.sort((a, b) => {
      // 1. Importance (descending: true > false)
      if (a.isImportant !== b.isImportant) {
        return a.isImportant ? -1 : 1;
      }
      // 2. Alphabetical (text asc)
      return a.text.localeCompare(b.text);
    });

    const sortedFinishedTasks = finishedTasks.sort((a, b) => {
      // Sorting finished tasks by ID descending usually implies newest completed first
      return b.id - a.id; 
    });

    return (
      <div>
        <form className="task-form" onSubmit={handleAddTask} style={{flexWrap: 'wrap'}}>
          <input 
            type="text" 
            placeholder="Add a new task..." 
            value={inputValue}
            onChange={(e) => setInputValue(e.target.value)}
          />
          <button type="submit">Add Task</button>
        </form>

        <div style={{marginBottom: '2rem'}} >
        <h3 style={{color: '#00b894', marginBottom: '1rem', fontSize: '1.1rem'}}>
          Current Tasks ({activeTasks.length})
        </h3> 

        <div className="notes-list">
          {sortedActiveTasks.length === 0 && <p style={{color:'#777'}}>No tasks yet.</p>}
          
          {sortedActiveTasks.map(task => ( 
            <div 
              key={task.id} 
              className={task.isImportant ? "note-card impTask" : "note-card"}
              style={{
                background: task.isDone ? '#e2e6ea' : (task.isImportant ? '#fff9db' : '#f0f4ff'),
                transition: 'all 0.3s ease',
                borderLeft: task.isImportant ? '4px solid #f1c40f' : '4px solid transparent'
              }}
            >
              {editId === task.id ? (
                <div style={{display:'flex', gap:'5px', width:'100%', flexWrap: 'wrap'}}>
                    <input type="text" value={editText} onChange={(e) => setEditText(e.target.value)} style={{flex: 2, padding:'5px', borderRadius:'5px', border:'1px solid #ccc'}} />
                    <button onClick={() => saveEdit(task.id)} style={{background:'#00b894', borderRadius: '8px', color: 'white', border: 'none', padding:'5px 10px'}}>Save</button>
                    <button onClick={() => setEditId(null)} style={{background:'#ff7675', borderRadius: '8px', color: 'white', border: 'none', padding:'5px 10px'}}>Cancel</button>
                </div>
              ) : (
                <>
                  <div style={{
                      flex: 1, 
                      display: 'flex', 
                      flexDirection: 'column',
                      opacity: task.isDone ? 0.5 : 1 
                  }}>
                     <span style={{
                         fontWeight: task.isImportant ? 'bold' : 'normal', 
                         fontSize: '1.05rem',
                         textDecoration: task.isDone ? 'line-through' : 'none',
                         color: task.isDone ? '#888' : '#222'
                     }}>
                        {task.text}
                     </span>
                  </div>

                  <div style={{display:'flex', gap:'5px', alignItems: 'center'}}>
                    <button onClick={() => toggleImportant(task)} title="Mark Important" style={{
                        background: '#f1c40f', 
                        padding: '5px 0', 
                        width: '35px', 
                        borderRadius: '10px', 
                        color: 'white', 
                        border: 'none', 
                        fontWeight: 'bold', 
                        cursor: 'pointer', 
                        opacity: task.isDone ? 0.5 : 1
                    }}>!</button>

                    <button onClick={() => handleMarkDone(task)} style={{background: '#00b894', padding: '5px 0', width: '35px', borderRadius: '10px', color:'white', border: 'none', cursor: 'pointer'}}>{task.isDone ? '↩' : '✔'}</button>

                    <button onClick={() => startEditing(task)} style={{
                        background: '#0D52BD', 
                        color:'white', 
                        padding: '5px 10px', 
                        borderRadius: '10px', 
                        border: 'none', 
                        cursor: 'pointer',
                        opacity: task.isDone ? 0.5 : 1
                    }}>Edit</button>

                    <button onClick={() => handleDelete(task.id)} style={{background: '#ff7675', padding: '5px 0', width: '35px', borderRadius: '10px', color:'white', border: 'none', cursor: 'pointer'}}>✘</button>
                  </div>
                </>
              )}
            </div>
          ))}
        </div>
      </div>
      
      <div style={{
        borderTop: '2px solid #eee',
        paddingTop: '1.5rem',
        marginTop: '1.5rem'
      }}>
        <div style={{display: 'flex', justifyContent: 'space-between', alignItems: 'center', marginBottom: '1rem'}}>
          <h3 style={{color: '#888', fontSize: '1.1rem', margin: 0}}>
            Finished Tasks ({finishedTasks.length})
          </h3>
          {finishedTasks.length > 0 && (
              <button 
                onClick={() => {
                  if (window.confirm('Clear all finished tasks?')) {
                      const formData = new URLSearchParams({ id: 0, type: 'clear_finished' });
                      fetch('delete.php', { method: 'POST', body: formData })
                        .then(res => res.json())
                        .then(data => {
                            if(data.status === 'success') {
                                setTasks(activeTasks); 
                            } else {
                                alert(data.message);
                            }
                        })
                        .catch(err => alert("Error: " + err));
                  }
                }}
                style={{
                  background: '#00b894',
                  color: 'white',
                  border: 'none',
                  padding: '0.4rem 0.8rem',
                  borderRadius: '6px',
                  fontSize: '0.85rem',
                  cursor: 'pointer'
                }}
              >
                Clear All
              </button>
          )}
        </div>
        
        {finishedTasks.length === 0 ? (
          <p style={{color:'#aaa', fontStyle: 'italic', textAlign: 'center', padding: '1rem'}}>
            No finished tasks yet. Complete some tasks to see them here!
          </p>
        ) : (
          <div style={{
            background: '#f8f9fa',
            borderRadius: '10px',
            padding: '1rem',
            maxHeight: '300px',
            overflowY: 'auto'
          }}>
            {sortedFinishedTasks.map(task => (
              <div 
                key={task.id} 
                style={{
                  padding: '0.8rem',
                  marginBottom: '0.7rem',
                  background: 'white',
                  borderRadius: '8px',
                  borderLeft: '4px solid #00b894',
                  display: 'flex',
                  justifyContent: 'space-between',
                  alignItems: 'center',
                  boxShadow: '0 2px 4px rgba(0,0,0,0.05)'
                }}
              >
                <div style={{flex: 1}}>
                  <span style={{
                    color: '#666',
                    textDecoration: 'line-through',
                    display: 'block',
                    marginBottom: '0.3rem',
                    fontSize: '0.95rem'
                  }}>
                    {task.text}
                  </span>
                </div>
                
                <div style={{display: 'flex', gap: '5px'}}>
                  <button 
                    onClick={() => handleMarkDone(task)}
                    title="Mark as Active"
                    style={{
                      background: '#6c757d',
                      padding: '0.3rem 0.6rem',
                      borderRadius: '6px',
                      color: 'white',
                      border: 'none',
                      cursor: 'pointer',
                      fontSize: '0.8rem'
                    }}
                  >
                    Undo
                  </button>
                  
                  <button 
                    onClick={() => handleDelete(task.id)}
                    style={{
                      background: '#00b894',
                      padding: '0.3rem 0.6rem',
                      borderRadius: '6px',
                      color: 'white',
                      border: 'none',
                      cursor: 'pointer',
                      fontSize: '0.8rem'
                    }}
                  >
                    Delete
                  </button>
                </div>
              </div>
            ))}
          </div>
        )}
      </div>
    </div>
    );
  }

  function NotesManager() {
    const [notes, setNotes] = React.useState([]);
    const [noteText, setNoteText] = React.useState("");

    React.useEffect(() => {
        fetch('fetch.php?type=note')
            .then(res => res.json())
            .then(data => setNotes(data))
            .catch(err => console.error(err));
    }, []);

    const handleAddNote = (e) => {
      e.preventDefault();
      if (!noteText.trim()) return;

      const formData = new FormData();
      formData.append('text', noteText);
      formData.append('type', 'note');
      
      fetch('save.php', { method: 'POST', body: formData })
        .then(res => res.json())
        .then(data => {
            if(data.status === 'success') {
                fetch('fetch.php?type=note')
                    .then(res => res.json())
                    .then(data => setNotes(data));
                setNoteText("");
            } else {
                alert(data.message);
            }
        });
    };

    const deleteNote = (id) => {
      if(!confirm("Delete note?")) return;
      
      const formData = new URLSearchParams({id: id, type: 'note'});
      fetch('delete.php', { method: 'POST', body: formData })
        .then(res => res.json())
        .then(data => {
            if(data.status === 'success') {
                setNotes(notes.filter(note => note.id !== id));
            }
        });
    };

    return (
      <div>
        <form onSubmit={handleAddNote} style={{display: 'flex', flexDirection: 'column', gap: '0.5rem'}}>
          <textarea 
            required 
            placeholder="Write a new note..." 
            value={noteText}
            onChange={(e) => setNoteText(e.target.value)}
            style={{
              minHeight: '70px', 
              padding: '0.8rem', 
              border: '1px solid #ccc', 
              borderRadius: '8px',
              fontFamily: 'inherit',
              resize: 'vertical'
            }}
          ></textarea>
          <button 
            type="submit" 
            style={{
              background: '#00b894', 
              color: 'white', 
              border: 'none', 
              padding: '0.6rem', 
              borderRadius: '6px', 
              fontWeight: 'bold', 
              cursor: 'pointer'
            }}
          >
            Add Note
          </button>
        </form>

        <div className="notes-list">
          {notes.length === 0 && <p style={{color:'#777', marginTop: '1rem'}}>No notes yet.</p>}

          {notes.map(note => (
            <div key={note.id} className="note-card" style={{display:'flex', flexDirection:'column', alignItems:'flex-start', background:'white', border:'1px solid #eee'}}>
              <div style={{width: '100%', display:'flex', justifyContent:'space-between', marginBottom:'5px'}}>
                <span style={{fontSize: '0.75rem', color: '#999', fontWeight:'bold'}}>{note.timestamp}</span>
                <button 
                  onClick={() => deleteNote(note.id)}
                  style={{background: 'transparent', color:'red', border: 'none', cursor: 'pointer', fontWeight:'bold'}}
                >
                  ✘
                </button>
              </div>
              <p style={{margin: 0, whiteSpace: 'pre-wrap', color: '#444'}}>{note.content || note.text}</p>
            </div>
          ))}
        </div>
      </div>
    );
  }

  const taskRoot = ReactDOM.createRoot(document.getElementById('task-root'));
  taskRoot.render(<TaskManager />);

  const noteRoot = ReactDOM.createRoot(document.getElementById('note-root'));
  noteRoot.render(<NotesManager />);
</script>

</body>
</html>
