import os
from dotenv import load_dotenv
import streamlit as st
from google import genai
import subprocess

# Explicitly load .env from the script's directory
basedir = os.path.abspath(os.path.dirname(__file__))
env_path = os.path.join(basedir, '.env')
load_dotenv(dotenv_path=env_path, override=True)

# Retrieve the API key
api_key = os.getenv("GEMINI_API_KEY")

if not api_key:
    st.error(f"API key not found! Checked path: {env_path}")
    st.stop()
else:
    client = genai.Client(api_key=api_key)
# Section 1: Local Terminal Runner
st.subheader("💻 Local Terminal Runner")
cmd_input = st.text_input("Enter command to run (e.g., php calculate.php):", value="php calculate.php")

if st.button("Execute Command"):
    if cmd_input.strip():
        with st.spinner("Running command in workspace..."):
            try:
                result = subprocess.run(
                    cmd_input,
                    shell=True,
                    capture_output=True,
                    text=True,
                    cwd=r"C:\OpenWork-DebugAgent"
                )
                
                if result.stdout:
                    st.subheader("Command Output:")
                    st.code(result.stdout)
                    
                if result.stderr:
                    st.subheader("Errors / Warnings:")
                    st.error(result.stderr)
                    
                if not result.stdout and not result.stderr:
                    st.success("Command executed successfully with no output.")
                    
            except Exception as e:
                st.error(f"Failed to execute command: {e}")
    else:
        st.warning("Please enter a command.")

st.markdown("---")

# Section 2: Code Agent Runner
st.subheader("🤖 Code Agent Runner")
user_prompt = st.text_area("Describe your task or paste code to fix:")

if st.button("Run Agent"):
    if user_prompt.strip():
        with st.spinner("Processing request..."):
            try:
                response = client.models.generate_content(
                    model="gemini-2.5-flash",
                    contents=user_prompt
                )
                
                output_code = response.text
                
                # Save the output to a local file
                file_path = r"C:\C:\OpenWork-DebugAgent\updated_script.php"
                with open(file_path, "w", encoding="utf-8") as f:
                    f.write(output_code)
                
                st.success(f"File successfully updated at {file_path}!")
                st.subheader("Agent Output")
                st.write(response.text)
                
            except Exception as e:
                st.error(f"An error occurred: {e}")
    else:
        st.warning("Please enter a prompt first.")
        
st.markdown("---")
st.subheader("🚀 Git Automation")

commit_message = st.text_input("Commit Message:", value="Update script via Streamlit agent")

if st.button("Commit & Push to GitHub"):
    if commit_message.strip():
        with st.spinner("Pushing code to GitHub..."):
            try:
                # 1. Stage changes
                subprocess.run("git add .", shell=True, check=True, cwd=r"C:\OpenWork-DebugAgent")
                
                # 2. Commit changes
                commit_cmd = f'git commit -m "{commit_message}"'
                subprocess.run(commit_cmd, shell=True, check=True, cwd=r"C:\OpenWork-DebugAgent")
                
                # 3. Push to remote repository
                push_result = subprocess.run(
                    "git push", 
                    shell=True, 
                    capture_output=True, 
                    text=True, 
                    cwd=r"C:\OpenWork-DebugAgent"
                )
                
                if push_result.returncode == 0:
                    st.success("Successfully pushed changes to GitHub!")
                    if push_result.stdout:
                        st.code(push_result.stdout)
                else:
                    st.error(f"Git push failed: {push_result.stderr}")
                    
            except subprocess.CalledProcessError as e:
                st.error(f"Git command failed: {e}")
    else:
        st.warning("Please enter a commit message.")        
        