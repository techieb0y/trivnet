/* Doug Gunnoe 2/2008 */
var level;
var printSTR = "";
var outDIV;
var stopAT;
var stack = [];


function parseDOMat(branchOfDOM, tgt, tgtAttribute){
  printSTR = "";
  level = 0;
  stopAT = branchOfDOM;
  walkThisBranch(branchOfDOM);
  if(tgtAttribute == 'innerHTML')
    tgt.innerHTML = printSTR;
  if(tgtAttribute == 'value')
    tgt.value = printSTR;
}


function walkThisBranch(branchOfDOM){
if ( branchOfDOM.hasChildNodes() ) {
  doStuff(branchOfDOM);
  branchOfDOM = branchOfDOM.firstChild;
  level++;
  walkThisBranch(branchOfDOM);
} else if ( branchOfDOM.nextSibling != null ) {
  doStuff(branchOfDOM);
  branchOfDOM = branchOfDOM.nextSibling;
  walkThisBranch(branchOfDOM);
} else {
    doStuff(branchOfDOM);
    while( branchOfDOM.nextSibling == null && branchOfDOM != stopAT ) {
    	branchOfDOM = branchOfDOM.parentNode;
    	level--;
    	//alert(stack);
    } // end while

    if( branchOfDOM != stopAT ) {
    	branchOfDOM = branchOfDOM.nextSibling;
    	walkThisBranch(branchOfDOM); 
    } else { 
    } // end if   

} // end if

} // end walkThisBranch

// Change this function to do whatever 
// you need done. I construct a string
// of the DOM tree, although I don't check
// to make sure the markup tags are correctly nested. 
function doStuff(nodex){
        // Useful part
	count = document.getElementById("counter").value;
        if ( nodex.id != undefined ) {
                nodex.id = nodex.id + count;
        } // end if
} // end doStuff
