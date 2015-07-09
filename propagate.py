import subprocess, os, sys

if __name__ != "__main__":
    print __file__ + " cannot be included in other scripts!"
    sys.exit()

usage = '''
Usage: 
python propagate.py
-> Updates all supported repos

python propagate.py [test_#]
-> Updates only the specified repo

python propagate.py [test_#] [branch name]
-> Updates the specified repo to the specified branch's state
'''

RED='\033[0;31m'
DEFAULT='\033[0m' # No Color

GIT = "/bin/git"
REPO_BASE = "/home/repos/"
REPOS = ["test_1", "test_39", "test_2", "test_3", "test_4", "test_5", "test_8"]
GITHUB = "nwayamerica/sharded_cstool.git"
SERVER_PREFIX = "test_"

def show_help():
    print RED + usage + DEFAULT
    sys.exit()

def get_branch_names():
    set_of_branches = []
    cmd = GIT + " ls-remote --heads git@github.com:" + GITHUB
    # print cmd
    branches = subprocess.check_output(cmd, stderr=subprocess.STDOUT, shell=True)

    # print branches
    
    for b in branches.split("\n"):
        if len(b) == 0:
            continue
        # print b
        strpos = b.rfind("/")
        branch_name = b[strpos + 1:]
        set_of_branches.append(branch_name)
        
    return set_of_branches
    
def verify_branch_exists(branch_name):
    if not branch_name:
        print "No such branch name? How did this even happen"
        sys.exit()
        
    set_of_branches = get_branch_names()
    if not branch_name in set_of_branches:
        print "Cannot update branch named '" + branch_name + "' as it does not exist. Extant branches:"
        for b in set_of_branches:
            print "\t" + b
        sys.exit()
        
    # print branch_name + " totalllly exists in"
    # print set_of_branches
        
    return

args = sys.argv
del args[0]

if len(args) >= 1 and (args[0] == 'help' or args[0] == '?'):
    show_help()

iterable_repos = REPOS
branch_name = "master"
if len(args) >= 1:
    if args[0].isdigit():
        iterable_repos = [SERVER_PREFIX + args[0]]
    elif args[0].find(SERVER_PREFIX) == -1:
        print "Invalid " + SERVER_PREFIX + "# argument."
        show_help()
    else:
        repos = [args[0]]

    if len(args) >= 2:
        branch_name = args[1]
        verify_branch_exists(branch_name)
        
for repo in repos:
    repo_path = REPO_BASE + repo
    if not os.path.isdir(repo_path):
        print "Path to repo doesn't exist: " + repo_path + ". Exiting."
        sys.exit()
        
    os.chdir(repo_path)
    
    cmd = [GIT, "reset", "origin/"+ branch_name, " --hard"]
    subprocess.call(cmd, stderr=subprocess.STDOUT, shell=False)
    subprocess.call(GIT + " fetch origin", stderr=subprocess.STDOUT, shell=True)
    subprocess.call(GIT + " pull origin master", stderr=subprocess.STDOUT, shell=True)